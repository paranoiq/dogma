<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: ino nlink uid gid rdev atime mtime ctime blksize

namespace Dogma\Io;

use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use function basename;
use function chgrp;
use function chmod;
use function chown;
use function clearstatcache;
use function dirname;
use function error_clear_last;
use function error_get_last;
use function file_exists;
use function fstat;
use function is_executable;
use function is_readable;
use function is_resource;
use function is_writable;
use function realpath;
use function scandir;
use function sprintf;
use function stat;
use function str_replace;
use function touch;

class Info implements Path
{
    use StrictBehaviorMixin;

    /** @var string */
    protected $path;

    /** @var int[]|string[]|null */
    protected $stat;

    /** @var resource|null */
    private $handle;

    /**
     * @param string|Path $file
     * @param resource|null $stat
     */
    public function __construct($file, $handle = null)
    {
        if ($file instanceof Path) {
            $this->path = $file->getPath();
        } else {
            $this->path = str_replace('\\', '/', $file);
        }
        $this->handle = $handle;
    }

    public function clearCache(): void
    {
        $this->stat = null;
        clearstatcache(true, $this->path);
    }

    protected function init(): void
    {
        error_clear_last();
        if (is_resource($this->handle)) {
            $stat = fstat($this->handle);
        } else {
            $stat = stat($this->path);
        }

        if ($stat === false) {
            throw new FileException('Cannot acquire file metadata.', error_get_last());
        }

        $this->stat = $stat;
    }

    public function getLinkInfo(): LinkInfo
    {
        if (!$this->isLink()) {
            throw new FileException('File is not a link.');
        }

        return new LinkInfo($this->path);
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function open(): File
    {
        $path = $this->getRealPath();

        return new File($path);
    }

    public function touch(): void
    {
        error_clear_last();
        $res = touch($this->path);
        if ($res === false) {
            throw new FileException('Cannot touch file.', error_get_last());
        }
    }

    public function changePermissions(int $permissions): void
    {
        error_clear_last();
        $res = chmod($this->path, $permissions);
        if ($res === false) {
            throw new FileException('Cannot change permissions.', error_get_last());
        }
    }

    public function changeOwner(int $ownerId): void
    {
        error_clear_last();
        $res = chown($this->path, $ownerId);
        if ($res === false) {
            throw new FileException('Cannot change file owner.', error_get_last());
        }
    }

    public function changeGroup(int $groupId): void
    {
        error_clear_last();
        $res = chgrp($this->path, $groupId);
        if ($res === false) {
            throw new FileException('Cannot change file owner.', error_get_last());
        }
    }

    // path ------------------------------------------------------------------------------------------------------------

    public function getName(): string
    {
        return basename($this->path);
    }

    public function getExtension(): ?string
    {
        [, $ext] = Str::splitByLast($this->getName(), '.');

        return $ext ?: null;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRealPath(): string
    {
        $path = realpath($this->path);
        if ($path === false) {
            throw new FileException(sprintf('File %s does not exits.', $this->path));
        }

        return $path;
    }

    public function getDirectory(): string
    {
        return dirname($this->path);
    }

    // file types ------------------------------------------------------------------------------------------------------

    public function getType(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['mode'] & 0770000;
    }

    public function isDirectory(): bool
    {
        return $this->getType() === FileType::DIRECTORY;
    }

    public function isDot(): bool
    {
        return $this->getName() === '.' || $this->getName() === '..';
    }

    public function isEmpty(): bool
    {
        if (!$this->isDirectory()) {
            throw new FileException('Path is not a directory.');
        }

        error_clear_last();
        $files = scandir($this->path);
        if ($files === false) {
            throw new FileException('Cannot read directory.', error_get_last());
        }

        return count($files) === 2;
    }

    public function isFile(): bool
    {
        return $this->getType() === FileType::FILE;
    }

    public function isLink(): bool
    {
        return $this->getType() === FileType::LINK;
    }

    public function isSocket(): bool
    {
        return $this->getType() === FileType::SOCKET;
    }

    public function isPipe(): bool
    {
        return $this->getType() === FileType::PIPE;
    }

    public function isBlockDevice(): bool
    {
        return $this->getType() === FileType::BLOCK_DEVICE;
    }

    public function isCharacterDevice(): bool
    {
        return $this->getType() === FileType::CHAR_DEVICE;
    }

    // permissions -----------------------------------------------------------------------------------------------------

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function isReadable(): bool
    {
        return is_readable($this->path);
    }

    public function isWritable(): bool
    {
        return is_writable($this->path);
    }

    public function isExecutable(): bool
    {
        return is_executable($this->path);
    }

    public function getPermissions(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['mode'] & 0777;
    }

    // stats -----------------------------------------------------------------------------------------------------------

    public function getLinksCount(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['nlink'];
    }

    public function getOwner(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['uid'];
    }

    public function getGroup(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['gid'];
    }

    public function getAccessTime(): DateTime
    {
        if ($this->stat === null) {
            $this->init();
        }

        return DateTime::createFromTimestamp($this->stat['atime']);
    }

    public function getModifyTime(): DateTime
    {
        if ($this->stat === null) {
            $this->init();
        }

        return DateTime::createFromTimestamp($this->stat['mtime']);
    }

    public function getInodeChangeTime(): DateTime
    {
        if ($this->stat === null) {
            $this->init();
        }

        return DateTime::createFromTimestamp($this->stat['ctime']);
    }

    public function getInode(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['ino'];
    }

    public function getDeviceId(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['dev'];
    }

    public function getDeviceType(): string
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['rdev'];
    }

    public function getSize(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['size'];
    }

    public function getBlockSize(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['blksize'];
    }

    public function getBlocks(): int
    {
        if ($this->stat === null) {
            $this->init();
        }

        return $this->stat['blocks'];
    }

}
