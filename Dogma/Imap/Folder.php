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


    /** @var \Dogma\Imap\Connection */
    private $imap;

    /** @var string */
    private $name;

    /** @var integer */
    private $attr;

    /** @var integer */
    private $messages;

    /** @var integer */
    private $recent;

    /** @var integer */
    private $unread;

    /** @var integer */
    private $deleted;

    /** @var integer */
    private $size;


    /**
     * @param \Dogma\Imap\Connection
     * @param string
     * @param integer
     */
    public function __construct(Connection $imap, $name, $attributes) {
        $this->imap = $imap;
        $this->name = $name;
        $this->attr = $attributes;
    }


    /**
     * Open (select) this folder.
     * @return \Dogma\Imap\Folder
     */
    public function select() {
        return $this->imap->selectFolder($this->name);
    }


    // info ------------------------------------------------------------------------------------------------------------


    /**
     * @return boolean
     */
    public function isSelected() {
        return $this->imap->getSelectedFolder() === $this->name;
    }


    /**
     * @return boolean
     */
    public function isSelectable() {
        return !($this->attr & self::NOT_SELECTABLE);
    }


    /**
     * @return boolean
     */
    public function hasSubfolders() {
        return (bool) $this->attr & self::HAS_CHILDREN;
    }


    /**
     * @return boolean
     */
    public function canHaveSubfolders() {
        return !($this->attr & self::NO_SUBFOLDERS);
    }


    /**
     * @return boolean
     */
    public function isSubscribed() {
        return $this->imap->isFolderSubscribed($this->name);
    }


    /**
     * @return integer
     */
    public function getMessageCount() {
        if (empty($this->messages)) $this->loadStatus();
        return $this->messages;
    }


    /**
     * @return integer
     */
    public function getRecentCount() {
        if (empty($this->recent)) $this->loadStatus();
        return $this->recent;
    }


    /**
     * @return integer
     */
    public function getUnreadCount() {
        if (empty($this->unread)) $this->loadStatus();
        return $this->unread;
    }


    /**
     * @return integer
     */
    public function getDeletedCount() {
        if (empty($this->deleted)) $this->loadInfo();
        return $this->deleted;
    }


    /**
     * @return integer
     */
    public function getSize() {
        if (empty($this->size)) $this->loadInfo();
        return $this->size;
    }


    // subfolders ------------------------------------------------------------------------------------------------------


    /**
     * @param string
     * @param boolean
     * @return string[]
     */
    public function listSubfolders($filter = "*", $all = true) {
        return $this->imap->listFolders($this->name . '/' . $filter, $all);
    }


    /**
     * @param string
     * @param boolean
     * @return \Dogma\Imap\Folder[]
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
