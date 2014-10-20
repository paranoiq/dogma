<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Imap;


class Folder extends \Dogma\Object {

    const
        NO_SUBFOLDERS = LATT_NOINFERIORS, // 1
        NOT_SELECTABLE = LATT_NOSELECT, // 2
        IS_MARKED = LATT_MARKED, // 4
        IS_UNMARKED = LATT_UNMARKED, // 8
        IS_REFERENCE = LATT_REFERRAL, // 16
        HAS_CHILDREN = LATT_HASCHILDREN, // 32
        HAS_NO_CHILDREN = LATT_HASNOCHILDREN; // 64


    /** @var Connection */
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


    public function __construct(Connection $imap, $name, $attributes) {
        $this->imap = $imap;
        $this->name = $name;
        $this->attr = $attributes;
    }


    /**
     * Open (select) this folder.
     * @return Folder
     */
    public function select() {
        return $this->imap->selectFolder($this->name);
    }


    // info ------------------------------------------------------------------------------------------------------------


    /**
     * @return bool
     */
    public function isSelected() {
        return $this->imap->getSelectedFolder() === $this->name;
    }


    /**
     * @return bool
     */
    public function isSelectable() {
        return !($this->attr & self::NOT_SELECTABLE);
    }


    /**
     * @return bool
     */
    public function hasSubfolders() {
        return (bool) $this->attr & self::HAS_CHILDREN;
    }


    /**
     * @return bool
     */
    public function canHaveSubfolders() {
        return !($this->attr & self::NO_SUBFOLDERS);
    }


    /**
     * @return bool
     */
    public function isSubscribed() {
        return $this->imap->isFolderSubscribed($this->name);
    }


    /**
     * @return int
     */
    public function getMessageCount() {
        if (empty($this->messages)) $this->loadStatus();
        return $this->messages;
    }


    /**
     * @return int
     */
    public function getRecentCount() {
        if (empty($this->recent)) $this->loadStatus();
        return $this->recent;
    }


    /**
     * @return int
     */
    public function getUnreadCount() {
        if (empty($this->unread)) $this->loadStatus();
        return $this->unread;
    }


    /**
     * @return int
     */
    public function getDeletedCount() {
        if (empty($this->deleted)) $this->loadInfo();
        return $this->deleted;
    }


    /**
     * @return int
     */
    public function getSize() {
        if (empty($this->size)) $this->loadInfo();
        return $this->size;
    }


    // subfolders ------------------------------------------------------------------------------------------------------


    /**
     * @param string
     * @param bool
     * @return string[]
     */
    public function listSubfolders($filter = "*", $all = true) {
        return $this->imap->listFolders($this->name . '/' . $filter, $all);
    }


    /**
     * @param string
     * @param bool
     * @return Folder[]
     */
    public function getSubfolders($filter = "*", $all = true) {
        return $this->imap->getFolders($this->name . '/' . $filter, $all);
    }


    // internals -------------------------------------------------------------------------------------------------------


    private function loadStatus() {
        $status = $this->imap->getFolderStatus($this->name);

        $this->messages = $status->messages;
        $this->recent = $status->recent;
        $this->unread = $status->unseen;
        // uidnext
        // uidvalidity
    }


    private function loadInfo() {
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
