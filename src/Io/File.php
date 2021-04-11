<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\Check;
use Dogma\Io\Stream\StreamInfo;
use Dogma\LogicException;
use Dogma\Math\PowersOfTwo;
use Dogma\NonCloneableMixin;
use Dogma\NonSerializableMixin;
use Dogma\StrictBehaviorMixin;
use StreamContext;
use const LOCK_UN;
use function basename;
use function dirname;
use function error_clear_last;
use function error_get_last;
use function fclose;
use function feof;
use function fflush;
use function flock;
use function fopen;
use function fseek;
use function ftell;
use function is_dir;
use function is_resource;
use function stream_get_meta_data;
use function stream_set_blocking;
use function stream_set_read_buffer;
use function stream_set_write_buffer;

/**
 * Common base for BinaryFile and TextFile
 */
abstract class File implements Path
{
    use StrictBehaviorMixin;
    use NonCloneableMixin;
    use NonSerializableMixin;

    /** @var positive-int */
    public static $defaultChunkSize = PowersOfTwo::_64K;

    /** @var string|null */
    protected $path;

    /** @var string */
    protected $mode;

    /** @var StreamContext|null */
    protected $context;

    /** @var resource|null */
    protected $handle;

    /** @var int|null */
    protected $lock;

    /** @var FileInfo|null */
    protected $fileInfo;

    /**
     * @param string|resource|FilePath|FileInfo $file
     * @param resource|null $streamContext
     */
    abstract public function __construct($file, string $mode = FileMode::OPEN_READ, ?StreamContext $context = null);

    /**
     * @return resource
     */
    public function getHandle()
    {
        if (!is_resource($this->handle)) {
            throw FilesystemException::create("File is already closed", $this->path, $this->context);
        }

        return $this->handle;
    }

    // info ------------------------------------------------------------------------------------------------------------

    public function getFileInfo(): FileInfo
    {
        if (!$this->fileInfo) {
            $this->fileInfo = new FileInfo($this);
        }

        return $this->fileInfo;
    }

    public function getStreamInfo(): StreamInfo
    {
        return new StreamInfo(stream_get_meta_data($this->getHandle()));
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getPath(): string
    {
        if ($this->path === null) {
            $info = $this->getStreamInfo();
            $this->path = Io::normalizePath($info->uri);
        }

        return $this->path;
    }

    public function getName(): string
    {
        return basename($this->getPath());
    }

    public function isOpen(): bool
    {
        return is_resource($this->handle);
    }

    // actions ---------------------------------------------------------------------------------------------------------

    /**
     * Move file to a new destination
     * Closes and reopens file in the process, tries to lock it if the file was locked before
     * Creates path when RECURSIVE is set
     *
     * @param string $destination
     * @param int $flags
     */
    public function rename(string $destination, int $flags = 0): void
    {
        Check::flags($flags, Io::RECURSIVE);

        $dir = dirname($destination);
        if (!is_dir($dir)) {
            throw FilesystemException::create("Path is not a directory", $destination);
        }

        $wasOpen = false;
        $lock = null;
        if ($this->isOpen()) {
            $wasOpen = true;
            $lock = $this->lock;
            $this->close();
        }

        // todo: does not work for previously opened file. must be created
        Io::rename($this->getPath(), $destination, $flags, $this->context);

        $this->path = $destination;
        if ($wasOpen) {
            $this->reopen();
        }
        if ($lock !== null) {
            $this->lock($lock);
        }
    }

    public function reopen(): void
    {
        if ($this->isOpen()) {
            throw new LogicException('The file is not closed.');
        }
        $this->mode = FileMode::getReopenMode($this->mode);

        error_clear_last();
        if ($this->context !== null) {
            $handle = @fopen($this->getPath(), $this->mode, false, $this->context->getResource());
        } else {
            $handle = @fopen($this->getPath(), $this->mode, false);
        }
        if ($handle === false) {
            throw FilesystemException::create("Cannot open file in mode '$this->mode'", $this->path, $this->context, error_get_last());
        }
        $this->handle = $handle;
    }

    public function close(): void
    {
        if (!is_resource($this->handle)) {
            $this->fileInfo = null;
            $this->handle = null;
            $this->lock = null;
            return;
        }

        error_clear_last();
        $result = @fclose($this->handle);

        if ($result === false) {
            throw FilesystemException::create("Cannot close file", $this->path, $this->context, error_get_last());
        }

        $this->fileInfo = null;
        $this->handle = null;
        $this->lock = null;
    }

    public function endOfFileReached(): bool
    {
        error_clear_last();
        $end = @feof($this->getHandle());

        if ($end === true) {
            $error = error_get_last();
            if ($error !== null) {
                throw FilesystemException::create("File reading interrupted", $this->path, $this->context, $error);
            }
        }

        return $end;
    }

    public function setPosition(int $position, ?int $from = null): void
    {
        if ($position < 0) {
            $position *= -1;
            $from = FilePosition::END;
        }
        if ($from === null) {
            $from = FilePosition::BEGINNING;
        }

        error_clear_last();
        $result = @fseek($this->getHandle(), $position, $from);

        if ($result !== 0) {
            throw FilesystemException::create("Cannot set file pointer position", $this->path, $this->context, error_get_last());
        }
    }

    public function getPosition(): int
    {
        error_clear_last();
        $position = @ftell($this->getHandle());

        if ($position === false) {
            throw FilesystemException::create("Cannot get file pointer position", $this->path, $this->context, error_get_last());
        }

        return $position;
    }

    // concurrency, buffering, flushing --------------------------------------------------------------------------------

    public function setBlocking(): void
    {
        error_clear_last();
        $result = @stream_set_blocking($this->handle, true);

        if ($result === false) {
            throw FilesystemException::create("Cannot set file to blocking mode", $this->path, $this->context, error_get_last());
        }
    }

    public function setNonBlocking(): void
    {
        error_clear_last();
        $result = @stream_set_blocking($this->handle, false);

        if ($result === false) {
            throw FilesystemException::create("Cannot set file to non-blocking mode", $this->path, $this->context, error_get_last());
        }
    }

    public function lock(?int $mode = null): void
    {
        if ($mode === null) {
            $mode = LockType::SHARED;
        } else {
            Check::enum($mode, LockType::SHARED, LockType::EXCLUSIVE, LockType::NON_BLOCKING);
        }

        error_clear_last();
        $wouldBlock = null;
        $result = @flock($this->getHandle(), $mode, $wouldBlock);

        if ($result === false) {
            if ($wouldBlock) {
                throw FilesystemException::create("Non-blocking lock cannot be acquired", $this->path, $this->context, error_get_last());
            } else {
                throw FilesystemException::create("Cannot lock file", $this->path, $this->context, error_get_last());
            }
        }

        $this->lock = $mode;
    }

    public function unlock(): void
    {
        error_clear_last();
        $result = flock($this->getHandle(), LOCK_UN);

        if ($result === false) {
            throw FilesystemException::create("Cannot unlock file", $this->path, $this->context, error_get_last());
        }

        $this->lock = null;
    }

    public function setReadBuffer(int $size): void
    {
        error_clear_last();
        $result = @stream_set_read_buffer($this->handle, $size);

        if ($result === false) {
            throw FilesystemException::create("Cannot set file read buffer size", $this->path, $this->context, error_get_last());
        }
    }

    public function setWriteBuffer(int $size): void
    {
        error_clear_last();
        $result = @stream_set_write_buffer($this->handle, $size);

        if ($result === false) {
            throw FilesystemException::create("Cannot set file write buffer size", $this->path, $this->context, error_get_last());
        }
    }

    /**
     * Flush the file output buffer (fsync)
     */
    public function flush(): void
    {
        error_clear_last();
        $result = @fflush($this->getHandle());

        if ($result === false) {
            throw FilesystemException::create("Cannot flush file cache", $this->path, $this->context, error_get_last());
        }
        $this->fileInfo = null;
    }

}
