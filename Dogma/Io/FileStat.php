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
 * @property-read integer $deviceId
 * @property-read integer $inode
 * @property-read integer $perms
 * @property-read integer $linksCount
 * @property-read integer $owner
 * @property-read integer $group
 * @property-read string $deviceType
 * @property-read integer $size
 * @property-read integer $aTime
 * @property-read integer $mTime
 * @property-read integer $cTime
 * @property-read integer $blockSize
 * @property-read integer $blocks
 */
class FileStat extends \Dogma\Object
{

    /** @var array */
    private $stat;

    /**
     * @param array
     */
    public function __construct($stat)
    {
        $this->stat = $stat;
    }

    /**
     * @return integer
     */
    public function getDeviceId()
    {
        return $this->stat['dev'];
    }

    /**
     * @return integer
     */
    public function getInode()
    {
        return $this->stat['ino'];
    }

    /**
     * @return integer
     */
    public function getPerms()
    {
        return $this->stat['mode'];
    }

    /**
     * @return integer
     */
    public function getLinksCount()
    {
        return $this->stat['nlinks'];
    }

    /**
     * @return integer
     */
    public function getOwner()
    {
        return $this->stat['uid'];
    }

    /**
     * @return integer
     */
    public function getGroup()
    {
        return $this->stat['gid'];
    }

    /**
     * @return string
     */
    public function getDeviceType()
    {
        return $this->stat['rdev'];
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->stat['size'];
    }

    /**
     * @return integer
     */
    public function getATime()
    {
        return $this->stat['atime'];
    }

    /**
     * @return integer
     */
    public function getMTime()
    {
        return $this->stat['mtime'];
    }

    /**
     * @return integer
     */
    public function getCTime()
    {
        return $this->stat['ctime'];
    }

    /**
     * @return integer
     */
    public function getBlockSize()
    {
        return $this->stat['blksize'];
    }

    /**
     * @return integer
     */
    public function getBlocks()
    {
        return $this->stat['blocks'];
    }

}
