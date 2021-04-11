<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: maxmemory

namespace Dogma\Io;

use Dogma\InvalidArgumentException;
use Dogma\LogicException;
use Dogma\ResourceType;
use StreamContext;
use function error_clear_last;
use function error_get_last;
use function fread;
use function ftruncate;
use function fwrite;
use function get_resource_type;
use function implode;
use function is_callable;
use function is_resource;
use function is_string;
use function min;
use function strlen;
use function tmpfile;

/**
 * An open file in "binary mode"
 * All length arguments are in bytes
 */
class BinaryFile extends File
{

    /**
     * @param string|resource|Path $file
     * @param string $mode
     * @param StreamContext|null $context
     */
    public function __construct($file, string $mode = FileMode::OPEN_READ, ?StreamContext $context = null)
    {
        if (is_resource($file) && get_resource_type($file) === ResourceType::STREAM) {
            $this->handle = $file;
            $this->mode = $mode;
            return;
        } elseif (is_string($file)) {
            $this->path = Io::normalizePath($file);
        } elseif ($file instanceof Path) {
            $this->path = $file->getPath();
        } else {
            throw new InvalidArgumentException('Argument $file must be a file path or a stream resource.');
        }

        $this->mode = $mode;
        $this->context = $context;

        if ($this->handle === null) {
            $this->reopen();
        }
    }

    /**
     * @return static
     */
    public static function createTemporaryFile(): self
    {
        error_clear_last();
        /** @var resource|false $handle */
        $handle = tmpfile();

        if ($handle === false) {
            throw FilesystemException::create("Cannot create a temporary file", null, null, error_get_last());
        }

        return new static($handle, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
    }

    public static function createMemoryFile(?int $maxSize = null): self
    {
        if ($maxSize === null) {
            return new static('php://memory', FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
        } else {
            return new static("php://temp/maxmemory:$maxSize", FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
        }
    }

    public function toTextFile(?string $encoding = null, ?string $lineEnding = null): TextFile
    {
        return new TextFile($this->getHandle(), $this->mode, $this->context, $encoding, $lineEnding);
    }

    // content ---------------------------------------------------------------------------------------------------------

    public function getContents(): string
    {
        if ($this->getPosition()) {
            $this->setPosition(0);
        }

        $results = [];
        while (!$this->endOfFileReached()) {
            $results[] = $this->read();
        }

        return implode('', $results);
    }

    public function read(?int $bytes = null): ?string
    {
        $bytes = $bytes ?? self::$defaultChunkSize;

        if (!FileMode::isReadable($this->mode)) {
            throw new LogicException('Cannot read - file opened in write only mode.');
        }

        error_clear_last();
        $data = @fread($this->getHandle(), $bytes);

        if ($data === false) {
            if ($this->endOfFileReached()) {
                throw FilesystemException::create("Cannot read from file, end of file was reached", $this->path, $this->context, error_get_last());
            } else {
                throw FilesystemException::create("Cannot read from file", $this->path, $this->context, error_get_last());
            }
        }

        return $data === '' ? null : $data;
    }

    public function write(string $data, ?int $bytes = null): void
    {
        error_clear_last();
        if ($bytes !== null) {
            $result = @fwrite($this->getHandle(), $data, $bytes);
        } else {
            $result = @fwrite($this->getHandle(), $data);
        }

        if ($result === false) {
            throw FilesystemException::create("Cannot write to file", $this->path, $this->context, error_get_last());
        }
    }

    /**
     * Truncate file and move pointer at the end
     * @param int $bytes
     */
    public function truncate(int $bytes = 0): void
    {
        error_clear_last();
        $result = @ftruncate($this->getHandle(), $bytes);

        if ($result === false) {
            throw FilesystemException::create("Cannot truncate file", $this->path, $this->context, error_get_last());
        }

        $this->setPosition($bytes);
    }

    /**
     * Copy range of data to another File or callback. Returns actual length of copied data.
     *
     * @param self|FileInfo|callable $destination
     * @param int|null $start
     * @param int $bytes
     * @param int|null $chunkSize
     * @return int
     */
    public function copyData($destination, ?int $start = null, int $bytes = 0, ?int $chunkSize = null): int
    {
        $chunkSize = $chunkSize ?? self::$defaultChunkSize;
        if ($start !== null) {
            $this->setPosition($start);
        }

        if ($destination instanceof FileInfo) {
            $destination = $destination->open(FileMode::CREATE_OR_APPEND_WRITE);
        }

        $done = 0;
        $chunk = $bytes ? min($bytes - $done, $chunkSize) : $chunkSize;
        while (!$this->endOfFileReached() && (!$bytes || $done < $bytes)) {
            $buffer = $this->read($chunk);
            if ($buffer === null) {
                return $done;
            }

            $done += strlen($buffer);

            if ($destination instanceof self) {
                $destination->write($buffer);

            } elseif (is_callable($destination)) {
                $destination($buffer);

            } else {
                throw new InvalidArgumentException('Destination must be File or callable!');
            }
        }

        return $done;
    }

}
