<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;


/**
 * @property-read int $deviceId
 * @property-read int $inode
 * @property-read int $perms
 * @property-read int $linksCount
 * @property-read int $owner
 * @property-read int $group
 * @property-read string $deviceType
 * @property-read int $size
 * @property-read int $aTime
 * @property-read int $mTime
 * @property-read int $cTime
 * @property-read int $blockSize
 * @property-read int $blocks
 */
class FileStat extends \Dogma\Object {

    /** @var array */
    private $stat;
    
    /**
     * @param array
     */
    public function __construct($stat) {
        $this->stat = $stat;
    }
    
    
    public function getDeviceId() {
        return $this->stat['dev'];
    }

    public function getInode() {
        return $this->stat['ino'];
    }

    public function getPerms() {
        return $this->stat['mode'];
    }

    public function getLinksCount() {
        return $this->stat['nlinks'];
    }

    public function getOwner() {
        return $this->stat['uid'];
    }

    public function getGroup() {
        return $this->stat['gid'];
    }

    public function getDeviceType() {
        return $this->stat['rdev'];
    }

    public function getSize() {
        return $this->stat['size'];
    }

    public function getATime() {
        return $this->stat['atime'];
    }

    public function getMTime() {
        return $this->stat['mtime'];
    }

    public function getCTime() {
        return $this->stat['ctime'];
    }

    public function getBlockSize() {
        return $this->stat['blksize'];
    }

    public function getBlocks() {
        return $this->stat['blocks'];
    }
    
}
