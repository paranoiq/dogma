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
use StreamContext;
use function basename;
use function clearstatcache;
use function decoct;
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
use function stat;
use function str_replace;

/**
 * Represents a file system path to a directory, file or a symbolic link target
 * Does not validate existence of the path
 *
 * @see LinkInfo for symbolic link specific things
 */
class FileInfo implements Path
{
    use StrictBehaviorMixin;

    /** @var string */
    protected $path;

    /** @var File|null */
    private $file;

    /**
     * @param string|Path|File $file
     */
    public function __construct($file)
    {
        if ($file instanceof File) {
            $this->path = $file->getPath();
            $this->file = $file;
        } elseif ($file instanceof Path) {
            $this->path = $file->getPath();
        } else {
            $this->path = Io::normalizePath($file);
        }
    }

    public function clearCache(): void
    {
        clearstatcache(true, $this->path);
    }

    /**
     * @return mixed[]
     */
    protected function stat(): array
    {
        error_clear_last();
        if ($this->file !== null && $this->file->isOpen()) {
            $stat = fstat($this->file->getHandle());
        } else {
            $stat = stat($this->path);
        }

        if ($stat === false) {
            throw FilesystemException::create("Cannot acquire file metadata", $this->path, null, error_get_last());
        }

        return $stat;
    }

    public function getLinkInfo(): LinkInfo
    {
        return new LinkInfo($this->path);
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function open(string $mode = FileMode::OPEN_READ, ?StreamContext $context = null): BinaryFile
    {
        return new BinaryFile($this->path, $mode, $context);
    }

    public function read(int $offset = 0, ?int $length = null, ?StreamContext $context = null): string
    {
        return Io::read($this->path, $offset, $length, $context);
    }

    /**
     * @param int $start
     * @param int|null $count
     * @param int $flags
     * @param StreamContext|null $context
     * @return string[]
     */
    public function readLines(int $start = 0, ?int $count = null, int $flags = 0, ?StreamContext $context = null): array
    {
        return Io::readLines($this->path, $start, $count, $flags, $context);
    }

    /**
     * @param string $data
     * @param int $flags (FILE_APPEND, LOCK_EX)
     * @param StreamContext|null $context
     * @return int
     */
    public function write(string $data, int $flags = 0, ?StreamContext $context = null): int
    {
        return Io::write($this->path, $data, $flags, $context);
    }

    /**
     * @param int|DateTime|null $time
     * @param int|DateTime|null $accessTime
     */
    public function touch($time = null, $accessTime = null): void
    {
        Io::touch($this->path, $time, $accessTime);
    }

    public function updatePermissions(int $add, int $remove, ?int $owner = null, ?int $group = null): void
    {
        Io::updatePermissions($this->path, $add, $remove, $owner, $group, Io::FOLLOW_SYMLINKS);
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
            throw FilesystemException::create("Cannot get real path, file does not exits", $this->path);
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
        return $this->stat()['mode'] & 0770000;
    }

    public function getTypeLetter(): string
    {
        return FileType::LETTERS[$this->getType()];
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
            throw FilesystemException::create("Path is not a directory", $this->path);
        }

        error_clear_last();
        $files = scandir($this->path);
        if ($files === false) {
            throw FilesystemException::create("Cannot read directory", $this->path, null, error_get_last());
        }
        foreach ($files as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            if (file_exists($this->path . '/' . $name)) {
                return false;
            }
        }

        return true;
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
        return $this->stat()['mode'] & FilePermissions::ALL;
    }

    public function getPermissionsOct(): string
    {
        return decoct($this->stat()['mode'] & FilePermissions::ALL);
    }

    public function getPermissionsString(): string
    {
        $perms = $this->stat()['mode'] & FilePermissions::ALL;

        return $this->getTypeLetter()
            . (($perms & FilePermissions::OWNER_READ) ? 'r' : '-')
            . (($perms & FilePermissions::OWNER_WRITE) ? 'w' : '-')
            . (($perms & FilePermissions::OWNER_EXECUTE) ? 'x' : '-')
            . (($perms & FilePermissions::GROUP_READ) ? 'r' : '-')
            . (($perms & FilePermissions::GROUP_WRITE) ? 'w' : '-')
            . (($perms & FilePermissions::GROUP_EXECUTE) ? 'x' : '-')
            . (($perms & FilePermissions::OTHER_READ) ? 'r' : '-')
            . (($perms & FilePermissions::OTHER_WRITE) ? 'w' : '-')
            . (($perms & FilePermissions::OTHER_EXECUTE) ? 'x' : '-');
    }

    // stats -----------------------------------------------------------------------------------------------------------

    public function getLinksCount(): int
    {
        return $this->stat()['nlink'];
    }

    public function getOwner(): int
    {
        return $this->stat()['uid'];
    }

    public function getGroup(): int
    {
        return $this->stat()['gid'];
    }

    public function getAccessed(): int
    {
        return $this->stat()['atime'];
    }

    public function getAccessedTime(): DateTime
    {
        return DateTime::createFromTimestamp($this->stat()['atime']);
    }

    public function getModified(): int
    {
        return $this->stat()['mtime'];
    }

    public function getModifiedTime(): DateTime
    {
        return DateTime::createFromTimestamp($this->stat()['mtime']);
    }

    public function getChanged(): int
    {
        return $this->stat()['ctime'];
    }

    public function getChangedTime(): DateTime
    {
        return DateTime::createFromTimestamp($this->stat()['ctime']);
    }

    public function getInode(): int
    {
        return $this->stat()['ino'];
    }

    public function getDeviceId(): int
    {
        return $this->stat()['dev'];
    }

    public function getDeviceType(): string
    {
        return $this->stat()['rdev'];
    }

    public function getSize(): int
    {
        return $this->stat()['size'];
    }

    public function getBlockSize(): int
    {
        return $this->stat()['blksize'];
    }

    public function getBlocks(): int
    {
        return $this->stat()['blocks'];
    }

}
