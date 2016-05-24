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
class FileStat
{
    use \Dogma\StrictBehaviorMixin;

    /** @var array */
    private $stat;

    /**
     * @param mixed[]
     */
    public function __construct(array $stat)
    {
        $this->stat = $stat;
    }

    public function getDeviceId(): int
    {
        return $this->stat['dev'];
    }

    public function getInode(): int
    {
        return $this->stat['ino'];
    }

    public function getPerms(): int
    {
        return $this->stat['mode'];
    }

    public function getLinksCount(): int
    {
        return $this->stat['nlinks'];
    }

    public function getOwner(): int
    {
        return $this->stat['uid'];
    }

    public function getGroup(): int
    {
        return $this->stat['gid'];
    }

    public function getDeviceType(): string
    {
        return $this->stat['rdev'];
    }

    public function getSize(): int
    {
        return $this->stat['size'];
    }

    public function getATime(): int
    {
        return $this->stat['atime'];
    }

    public function getMTime(): int
    {
        return $this->stat['mtime'];
    }

    public function getCTime(): int
    {
        return $this->stat['ctime'];
    }

    public function getBlockSize(): int
    {
        return $this->stat['blksize'];
    }

    public function getBlocks(): int
    {
        return $this->stat['blocks'];
    }

}
