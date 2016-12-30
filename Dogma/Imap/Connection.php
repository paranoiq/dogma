<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Imap;

use Dogma\Mail\Message;

/**
 * IMAP connection
 */
class Connection
{
    use \Dogma\StrictBehaviorMixin;

    /** @var int */
    public static $connectionRetries = 1;


    /** @var resource */
    private $handler;

    /** @var string */
    private $ref;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var bool */
    private $ssl;

    /** @var string */
    private $user;

    /** @var string */
    private $password;


    /** @var string */
    private $selectedFolder;

    /** @var \Dogma\Imap\Folder[] */
    private $folders = [];

    /** @var \Dogma\Imap\MessageInfo[] */
    private $messages = [];

    /** @var string[] cache of subscribed folders */
    private $subscribed;


    /** @var callable */
    private $messageFactory;

    public function __construct(string $user, string $password, string $host = '127.0.0.1', int $port = 143, bool $ssl = false)
    {
        $this->user = $user;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->ssl = $ssl;

        $this->messageFactory = function ($data) {
            return $this->createMessage($data);
        };
    }

    public function setMessageFactory(callable $factory)
    {
        $this->messageFactory = $factory;
    }

    /**
     * Connect to server
     */
    public function connect()
    {
        $params = $this->ssl ? '/ssl/novalidate-cert' : ''; // /ssl/secure

        $options = 0;
        if (!$this->selectedFolder) {
            $options |= OP_HALFOPEN;
        }

        ///
        $this->handler = imap_open(
            sprintf('{%s:%s%s}%s', $this->host, $this->port, $params, $this->selectedFolder),
            $this->user,
            $this->password,
            $options,
            self::$connectionRetries
        );
        ///
        if (!$this->handler) {
            throw new ImapException(sprintf('Cannot connect to server: %s', imap_last_error()));
        }

        $this->ref = sprintf('{%s:%s}', $this->host, $this->port);
    }

    /**
     * Check if still connected
     */
    public function isConnected(): bool
    {
        if (!$this->handler) {
            return false;
        }

        ///
        return imap_ping($this->handler);
        ///
    }

    /**
     * Disconnect from server
     */
    public function disconnect()
    {
        ///
        $res = imap_close($this->handler, CL_EXPUNGE);
        ///
        if (!$res) {
            throw new ImapException('Error when disconnecting from server: ' . imap_last_error());
        }
    }

    /**
     * @return array
     */
    public function getQuota()
    {
        ///
        $q = imap_get_quotaroot($this->handler, 'INBOX');
        ///

        return [$q['storage']];
    }

    // folders ---------------------------------------------------------------------------------------------------------

    /**
     * Get tree structure of folders in mailbox
     * @param string
     * @param bool
     * @return array
     */
    public function getFolderStructure(string $filter = '*', bool $all = true): array
    {
        $folders = $this->listFolders($filter, $all);
        $struct = [];

        foreach ($folders as &$folder) {
            $parts = explode('/', $folder);
            $n = -1;
            $branch = &$struct;
            while (isset($parts[++$n])) {
                if (!isset($branch[$parts[$n]])) {
                    $branch[$parts[$n]] = [];
                }
                $branch = &$branch[$parts[$n]];
            }
        }

        return $struct;
    }

    /**
     * Return list of folders in mailbox
     * @param string
     * @param bool
     * @return array
     */
    public function listFolders(string $filter = '*', bool $all = true): array
    {
        ///
        if ($all) {
            $folders = imap_list($this->handler, $this->ref, $filter);
        } else {
            $folders = imap_lsub($this->handler, $this->ref, $filter);
        }
        ///

        foreach ($folders as &$folder) {
            $folder = preg_replace('/^Inbox(?=\\W)/i', 'INBOX', substr($this->decode($folder), strlen($this->ref)));
        }
        sort($folders);

        return $folders;
    }

    /**
     * Get info about folders
     * @param string
     * @param bool
     * @return \Dogma\Imap\Folder[]
     */
    public function getFolders(string $filter = '*', bool $all = true): array
    {
        ///
        if ($all) {
            $folders = imap_getmailboxes($this->handler, $this->ref, $filter);
        } else {
            $folders = imap_getsubscribed($this->handler, $this->ref, $filter);
        }
        ///

        $info = [];
        foreach ($folders as &$folder) {
            $name = preg_replace('/^Inbox(?=\\W)/i', 'INBOX', substr($this->decode($folder->name), strlen($this->ref)));

            if (empty($this->folders[$name])) {
                $this->folders[$name] = new Folder($this, $name, $folder->attributes);
            }
            $info[$name] = $this->folders[$name];
        }
        ksort($info);

        return array_values($info);
    }

    /**
     * Get IMAP folder status.
     * @param string
     * @return object
     */
    public function getFolderStatus(string $name)
    {
        ///
        return imap_status($this->handler, $this->ref . $this->encode($name), SA_ALL);
        ///
    }

    /**
     * Get IMAP folder info.
     * @param string
     * @return object
     */
    public function getFolderInfo(string $name)
    {
        if ($name !== $this->selectedFolder) {
            throw new \Nette\InvalidStateException('Folder must be open to read info.');
        }

        ///
        return imap_mailboxmsginfo($this->handler);
        ///
    }

    // subscriptions ---------------------------------------------------------------------------------------------------

    /**
     * Return list of subscribed folders in mailbox
     * @param string
     * @return array
     */
    public function listSubscribedFolders(string $filter = '*'): array
    {
        if ($filter === '*') {
            if (!$this->subscribed) {
                $this->subscribed = $this->listFolders('*', false);
            }
            return $this->subscribed;
        }

        return $this->listFolders($filter, false);
    }

    /**
     * Return list of unsubscribed folders in mailbox
     * @param string
     * @return array
     */
    public function listUnsubscribedFolders(string $filter = '*'): array
    {
        $all = $this->listFolders($filter);
        $sub = $this->listFolders($filter, false);

        return array_diff($all, $sub);
    }

    /**
     * Mark folder as subscribed (visible)
     */
    public function subscribeFolder(string $path)
    {
        ///
        imap_subscribe($this->handler, $this->ref . $this->encode($path));
        ///
    }

    /**
     * Mark folder as unsubscribed (invisible)
     */
    public function unsubscribeFolder(string $path)
    {
        ///
        imap_unsubscribe($this->handler, $this->ref . $this->encode($path));
        ///
    }

    /**
     * Tells if given folder is subscribed
     */
    public function isFolderSubscribed(string $name): bool
    {
        if (!$this->subscribed) {
            $this->subscribed = $this->listFolders('*', false);
        }

        return in_array($name, $this->subscribed);
    }

    // folder manipulation ---------------------------------------------------------------------------------------------

    /**
     * Select folder and return a Folder object
     */
    public function selectFolder(string $name): Folder
    {
        ///
        imap_reopen($this->handler, $this->ref . $this->encode($name), 0, self::$connectionRetries);
        ///
        $this->selectedFolder = $name;

        if (isset($this->folders[$name])) {
            return $this->folders[$name];
        }

        ///
        $f = imap_getmailboxes($this->handler, $this->ref, $name);
        ///
        return $this->folders[$name] = new Folder($this, $name, $f[0]->attributes);
    }

    /**
     * Returns name of selected folder
     * @return string|null
     */
    public function getSelectedFolder()
    {
        return $this->selectedFolder;
    }

    /**
     * Create new folder in mailbox
     */
    public function createFolder(string $path)
    {
        ///
        imap_createmailbox($this->handler, $this->ref . $this->encode($path));
        ///
    }

    /**
     * Delete folder from mailbox
     */
    public function deleteFolder(string $path)
    {
        ///
        imap_deletemailbox($this->handler, $this->ref . $this->encode($path));
        ///
    }

    /**
     * Rename folder to a new name
     */
    public function renameFolder(string $oldPath, string $newPath)
    {
        ///
        imap_renamemailbox($this->handler, $this->ref . $this->encode($oldPath), $this->ref . $this->encode($newPath));
        ///
    }

    // messages --------------------------------------------------------------------------------------------------------

    /**
     * Get list of messages from current folder.
     * @param array
     * @param string (date|arrival|from|subject|to|cc|size)
     * @param bool
     * @return \Dogma\Mail\Message[]
     */
    public function getMessages(array $criteria = [], string $orderBy = null, bool $descending = false): array
    {
        static $ob = [
            'date' => SORTDATE,
            'arrival' => SORTARRIVAL,
            'from' => SORTFROM,
            'subject' => SORTSUBJECT,
            'to' => SORTTO,
            'cc' => SORTCC,
            'size' => SORTSIZE,
        ];

        $crit = $criteria === [] ? 'ALL' : $this->compileCriteria($criteria);

        if ($orderBy) {
            if (!isset($ob[$orderBy])) {
                throw new \InvalidArgumentException(sprintf('Unknown sort criterion: %s', $orderBy));
            }

            $uids = imap_sort($this->handler, $ob[$orderBy], ($descending ? 1 : 0), SE_UID, $crit, 'UTF-8');
        } else {
            $uids = imap_search($this->handler, $crit, SE_UID, 'UTF-8');
        }
        if (!$uids && $e = imap_errors()) {
            throw new ImapException('IMAP search failed: ' . implode('; ', $e));
        }

        $messages = [];
        foreach ($uids as $uid) {
            if (empty($this->messages[$uid])) {
                $this->messages[$uid] = new MessageInfo($this, $uid);
            }
            $messages[$uid] = $this->messages[$uid];
        }

        return array_values($messages);
    }

    /**
     * Compile IMAP search criteria
     *
     * http://www.afterlogic.com/mailbee-net/docs/MailBee.ImapMail.Imap.Search_overload_1.html
     * @param array
     * @return string
     */
    private function compileCriteria(array $criteria): string
    {
        static $true = ['OLD', 'NEW', 'RECENT']; // NEW = RECENT & UNSEEN; OLD = NOT RECENT;
        static $bool = ['ANSWERED', 'DELETED', 'FLAGGED', 'SEEN'/*, 'DRAFT'*/];
        static $text = ['SUBJECT', 'BODY', 'TEXT', 'FROM', 'TO', 'CC', 'BCC'];
        static $date = ['ON', 'SINCE', 'BEFORE'/*, 'SENTON', 'SENTSINCE', 'SENTBEFORE'*/];
        /*static $size = ['LARGER', 'SMALLER'];*/
        // NOT %1
        // OR %1 %2

        $query = [];
        foreach ($criteria as $name => $value) {
            $name = strtoupper($name);

            if (in_array($name, $true)) {
                $query[] = /*($value ? '' : 'NOT ') . */$name;

            } elseif (in_array($name, $bool)) {
                $query[] = ($value ? '' : 'UN') . $name;

            } elseif (in_array($name, $text)) {
                $query[] = $name . ' "' . $value . '"';

            } elseif (in_array($name, $date)) {
                if (is_string($value)) {
                    $value = new \DateTime($value);
                }
                if (!$value instanceof \DateTime) {
                    throw new \InvalidArgumentException(sprintf('Given value of \'%s\' must be a DateTime or string.', $name));
                }
                $query[] = $name . ' ' . $value->format('d-M-Y');
            /*
            } elseif (in_array($name, $size)) {
                $query[] = $name . ' ' . $value;
            */
            } elseif ($name === 'KEYWORD') {
                $value = (array) $value;
                foreach ($value as $keyword) {
                    if ($keyword[0] === '-') {
                        $query[] = 'UNKEYWORD "' . substr($keyword, 1) . '"';
                    } else {
                        $query[] = 'KEYWORD "' . $keyword . '"';
                    }
                }
            /*
            } elseif ($name === 'UID') {
                $value = (array) $value;
                $query[] = 'UID "' . implode(',', $value) . '"';
            */
            } else {
                throw new \InvalidArgumentException(sprintf('Unknown search option \'%s\' given.', $name));
            }
        }

        return implode(' ', $query);
    }

    /**
     * Get Message object
     */
    public function getMessage(int $uid): Message
    {
        return call_user_func($this->messageFactory, $this->getRawMessageHeader($uid) . "\r\n\r\n" . $this->getMessageBody($uid));
    }

    /**
     * Retrieve message body.
     * @param int
     * @return string
     */
    public function getMessageBody(int $uid): string
    {
        ///
        return imap_body($this->handler, $uid, FT_UID | FT_PEEK);
        ///
    }

    public function getRawMessageHeader(int $uid): string
    {
        ///
        return imap_fetchheader($this->handler, $uid, FT_UID | FT_PREFETCHTEXT);
        ///
    }

    // internals -------------------------------------------------------------------------------------------------------

    /**
     * Encode from UTF-8 to UTF-7
     */
    private function encode(string $str): string
    {
        return mb_convert_encoding($str, 'UTF7-IMAP', 'UTF-8');
    }

    /**
     * Decode from UTF-7 to UTF-8
     */
    private function decode(string $str): string
    {
        return mb_convert_encoding($str, 'UTF-8', 'UTF7-IMAP');
    }

    private function createMessage(string $data): Message
    {
        $message = new Message($data);
        $message->setAddressFactory('Dogma\\Mail\\Message::createAddress');

        return $message;
    }

}
