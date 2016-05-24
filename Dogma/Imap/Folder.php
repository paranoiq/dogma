<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Imap;


class Folder
{
    use \Dogma\StrictBehaviorMixin;

    const NO_SUBFOLDERS = LATT_NOINFERIORS; // 1
    const NOT_SELECTABLE = LATT_NOSELECT; // 2
    const IS_MARKED = LATT_MARKED; // 4
    const IS_UNMARKED = LATT_UNMARKED; // 8
    const IS_REFERENCE = LATT_REFERRAL; // 16
    const HAS_CHILDREN = LATT_HASCHILDREN; // 32
    const HAS_NO_CHILDREN = LATT_HASNOCHILDREN; // 64


    /** @var \Dogma\Imap\Connection */
    private $imap;

    /** @var string */
    private $name;

    /** @var int */
    private $attr;

    /** @var int */
    private $messages;

    /** @var int */
    private $recent;

    /** @var int */
    private $unread;

    /** @var int */
    private $deleted;

    /** @var int */
    private $size;

    public function __construct(Connection $imap, string $name, int $attributes)
    {
        $this->imap = $imap;
        $this->name = $name;
        $this->attr = $attributes;
    }

    /**
     * Open (select) this folder.
     */
    public function select(): Folder
    {
        return $this->imap->selectFolder($this->name);
    }

    // info ------------------------------------------------------------------------------------------------------------

    public function isSelected(): bool
    {
        return $this->imap->getSelectedFolder() === $this->name;
    }

    public function isSelectable(): bool
    {
        return !($this->attr & self::NOT_SELECTABLE);
    }

    public function hasSubfolders(): bool
    {
        return (bool) $this->attr & self::HAS_CHILDREN;
    }

    public function canHaveSubfolders(): bool
    {
        return !($this->attr & self::NO_SUBFOLDERS);
    }

    public function isSubscribed(): bool
    {
        return $this->imap->isFolderSubscribed($this->name);
    }

    public function getMessageCount(): int
    {
        if (empty($this->messages)) {
            $this->loadStatus();
        }
        return $this->messages;
    }

    public function getRecentCount(): int
    {
        if (empty($this->recent)) {
            $this->loadStatus();
        }
        return $this->recent;
    }

    public function getUnreadCount(): int
    {
        if (empty($this->unread)) {
            $this->loadStatus();
        }
        return $this->unread;
    }

    public function getDeletedCount(): int
    {
        if (empty($this->deleted)) {
            $this->loadInfo();
        }
        return $this->deleted;
    }

    public function getSize(): int
    {
        if (empty($this->size)) {
            $this->loadInfo();
        }
        return $this->size;
    }

    // subfolders ------------------------------------------------------------------------------------------------------

    /**
     * @param string
     * @param bool
     * @return string[]
     */
    public function listSubfolders(string $filter = '*', bool $all = true): array
    {
        return $this->imap->listFolders($this->name . '/' . $filter, $all);
    }

    /**
     * @param string
     * @param bool
     * @return \Dogma\Imap\Folder[]
     */
    public function getSubfolders(string $filter = '*', bool $all = true): array
    {
        return $this->imap->getFolders($this->name . '/' . $filter, $all);
    }

    // internals -------------------------------------------------------------------------------------------------------

    private function loadStatus()
    {
        $status = $this->imap->getFolderStatus($this->name);

        $this->messages = $status->messages;
        $this->recent = $status->recent;
        $this->unread = $status->unseen;
        // uidnext
        // uidvalidity
    }

    private function loadInfo()
    {
        $info = $this->imap->getFolderInfo($this->name);

        // Date
        // Driver
        // Mailbox
        $this->messages = $info->Nmsgs;
        $this->recent = $info->Recent;
        $this->unread = $info->Unread;
        $this->deleted = $info->Deleted;
        $this->size = $info->Size;
    }

}
