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
 * IMAP message info.
 */
class MessageInfo
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Imap\Connection */
    private $imap;

    /** @var int */
    private $uid;

    /** @var \Dogma\Mail\Message */
    private $message;

    public function __construct(Connection $imap, int $uid)
    {
        $this->imap = $imap;
        $this->uid = $uid;
    }

    public function getMessage(): Message
    {
        if (!$this->message) {
            $this->message = $this->imap->getMessage($this->uid);
        }

        return $this->message;
    }

    /*public function getBody(): string
    {
        return $this->imap->getMessageBody($this->uid);
    }*/

    /*public function getRawHeader(): string
    {
        return $this->imap->getRawMessageHeader($this->uid);
    }*/

}
