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
 * Binary file reader/writer
 *
 * @property FileStat $info
 */
class File
{
    use \Dogma\StrictBehaviorMixin;

    // File opening mode:
    // if not found: ERROR; keep content
    const READ = 'rb';
    const READ_WRITE = 'r+b';
    // if not found: create; truncate content
    const TRUNCATE_WRITE = 'wb';
    const TRUNCATE_READ_WRITE = 'w+b';
    // if not found: create; keep content, point to end of file, don't accept new position
    const APPEND_WRITE = 'ab';
    const APPEND_READ_WRITE = 'a+b';
    // if found: ERROR; no content
    const CREATE_WRITE = 'xb';
    const CREATE_READ_WRITE = 'x+b';
    // if not found: create; keep content
    const OPEN_CREATE_WRITE = 'cb';
    const OPEN_CREATE_READ_WRITE = 'c+b';


    // Position from:
    const BEGINNING = 0;
    const CURRENT = 1;
    const END = 2;


    // Lock type:
    const SHARED = 1;
    const EXCLUSIVE = 2;
    const UNLOCK = 3;
    const NON_BLOCKING = 4;


    /**
     * @var int Set this *same or greater* then the allocation unit of your storage (disk sector, RAID strip etc.)
     */
    public static $defaultChunkSize = 8192;


    /** @var string file name */
    protected $name;

    /** @var resource */
    protected $streamContext;

    /** @var resource file descriptor */
    protected $file;

    /** @var \Dogma\Io\FileStat */
    private $stat;

    /**
     * Open file
     * @param string|resource
     * @param string
     * @param resource
     */
    public function __construct($file, string $mode = self::READ_WRITE, $streamContext = null)
    {
        if ($file === null) {
            return;
        }

        if (is_resource($file) && get_resource_type($file) === 'stream') {
            $this->file = $file;
            return;
        }

        $this->name = (string) $file;

        ///
        if ($streamContext) {
            $this->streamContext = $streamContext;
            $this->file = fopen($this->name, $mode, false, $streamContext);
        } else {
            $this->file = fopen($this->name, $mode, false);
        }

        if ($this->file === false) {
            throw new FileException(sprintf('Cannot open file in mode \'%s\'.', $mode), 0);
        }
    }

    public function __destruct()
    {
        if ($this->file) {
            fclose($this->file);
        }
    }

    public function isOpen(): bool
    {
        return (bool) $this->file;
    }

    /**
     * Close file
     */
    public function close()
    {
        $this->testOpen();

        ///
        $result = fclose($this->file);

        if ($result === false) {
            throw new FileException('Cannot close file.');
        }
        $this->stat = null;
        $this->file = null;
    }

    /**
     * End of file reached?
     */
    public function eof(): bool
    {
        $this->testOpen();

        ///
        $feof = feof($this->file);
        ///

        return $feof;
    }

    /**
     * Read binary data from file
     */
    public function read(int $length = null): string
    {
        if (empty($length)) {
            $length = self::$defaultChunkSize;
        }
        $this->testOpen();

        ///
        $data = fread($this->file, $length);

        if ($data === false) {
            if ($this->eof()) {
                throw new FileException('Cannot read data from file. End of file was reached.');
            } else {
                throw new FileException('Cannot read data from file.');
            }
        }

        return $data;
    }

    /**
     * Copy range of data to another File or callback. Returns actual length of copied data.
     * @param \Dogma\Io\File|callable
     * @param int
     * @param int
     * @return int
     */
    public function copyData($destination, int $start = null, int $length = 0, int $chunkSize = null): int
    {
        if (empty($chunkSize)) {
            $chunkSize = self::$defaultChunkSize;
        }
        if (!empty($start)) {
            $this->setPosition($start);
        }

        $done = 0;
        $chunk = $length ? min($length - $done, $chunkSize) : $chunkSize;
        while (!$this->eof() && (!$length || $done < $length)) {
            $buff = $this->read($chunk);
            $done += strlen($buff);

            if ($destination instanceof File) {
                $destination->write($buff);

            } elseif (is_callable($destination)) {
                call_user_func($destination, $buff);

            } else {
                throw new \InvalidArgumentException('Destination must be File or callable!');
            }
        }

        return $done;
    }

    /**
     * Get entire content of file. Beware of BIG files!
     */
    public function getContent(): string
    {
        if ($this->getPosition()) {
            $this->setPosition(0); // ?
        }

        $str = '';
        while (!$this->eof()) {
            $str .= $this->read();
        }

        return $str;
    }

    /**
     * Write binary data to file
     */
    public function write(string $data)
    {
        $this->testOpen();

        ///
        $result = fwrite($this->file, $data);

        if ($result === false) {
            throw new FileException('Cannot write data to file.');
        }
    }

    /**
     * Truncate file and move pointer at the end
     */
    public function truncate(int $size = 0)
    {
        $this->testOpen();

        ///
        $result = ftruncate($this->file, $size);

        if ($result === false) {
            throw new FileException('Cannot truncate file.');
        }

        $this->setPosition($size);
    }

    /**
     * Flush the file output buffer (fsync)
     */
    public function flush()
    {
        $this->testOpen();

        ///
        $result = fflush($this->file);

        if ($result === false) {
            throw new FileException('Cannot flush file cache.');
        }
        $this->stat = null;
    }

    /**
     * Lock file. see PHP flock() documentation
     */
    public function lock(int $mode = self::SHARED, int &$wouldBlock = null)
    {
        $this->testOpen();

        $wb = null;
        ///
        $result = flock($this->file, $mode, $wb);

        if ($result === false) {
            if ($wb) {
                $wouldBlock = $wb;
                throw new FileException('Non-blocking lock cannot be acquired.');
            } else {
                throw new FileException('Cannot lock file.');
            }
        }
    }

    /**
     * Release file lock
     */
    public function unlock()
    {
        $this->testOpen();

        ///
        $result = flock($this->file, LOCK_UN);

        if ($result === false) {
            throw new FileException('Cannot unlock file.');
        }
    }

    /**
     * Set the file pointer position
     * @param int|bool position in bytes or true for end of file
     * @param int $from
     */
    public function setPosition($position, $from = self::BEGINNING)
    {
        $this->testOpen();

        if ($position === true) {
            $position = 0;
            $from = SEEK_END;
        }

        ///
        $result = fseek($this->file, $position, $from);

        if ($result !== 0) {
            throw new FileException('Cannot set file pointer position.');
        }
    }

    /**
     * Get file pointer position
     */
    public function getPosition(): int
    {
        $this->testOpen();

        ///
        $position = ftell($this->file);

        if ($position === false) {
            throw new FileException('Cannot get file pointer position.');
        }

        return $position;
    }

    /**
     * Get file name
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            $meta = $this->getMetaData();
            $this->name = $meta['uri'];
        }

        return $this->name;
    }

    /**
     * Get file info
     */
    public function getInfo(): FileStat
    {
        if (!$this->stat) {
            if ($this->file) {
                if (!$stat = @fstat($this->file)) {
                    throw new FileException('Cannot acquire file metadata.');
                }
            } else {
                if (empty($this->name)) {
                    $this->getName();
                }

                if (!$stat = @stat($this->name)) {
                    throw new FileException('Cannot acquire file metadata.');
                }
            }
            $this->stat = new FileStat($stat);
        }

        return $this->stat;
    }

    /**
     * Get stream meta data for files opened via HTTP, FTP…
     * @return mixed[]
     */
    public function getMetaData(): array
    {
        return stream_get_meta_data($this->file);
    }

    /**
     * Get stream wrapper headers (HTTP)
     * @return mixed[]
     */
    public function getWrapperData(): array
    {
        $data = stream_get_meta_data($this->file);

        return $data['wrapper_data'];
    }

    //public function getResponseContext()
    //{
        ///
    //}


    /*
    [
        [wrapper_data] => [
            [0] => HTTP/1.1 200 OK
            [1] => Server: Apache/2.2.3 (Red Hat)
            [2] => Last-Modified: Tue, 15 Nov 2005 13:24:10 GMT
            [3] => ETag: "b300b4-1b6-4059a80bfd280"
            [4] => Accept-Ranges: bytes
            [5] => Content-Type: text/html; charset=UTF-8
            [6] => Set-Cookie: FOO=BAR; expires=Fri, 21-Dec-2012 12:00:00 GMT; path=/; domain=.example.com
            [6] => Connection: close
            [7] => Date: Fri, 16 Oct 2009 12:00:00 GMT
            [8] => Age: 1164
            [9] => Content-Length: 438
        ]
        [wrapper_type] => http
        [stream_type] => tcp_socket/ssl
        [mode] => r
        [unread_bytes] => 438
        [seekable] =>
        [uri] => http://www.example.com/
        [timed_out] =>
        [blocked] => 1
        [eof] =>
    ]
    */

    // factories -------------------------------------------------------------------------------------------------------

    public static function createTemporaryFile(): self
    {
        ///
        $fd = tmpfile();

        if (!$fd) {
            throw new FileException('Cannot create a temporary file.');
        }
        $file = new static(null, 'w+');
        $file->file = $fd;

        return $file;
    }

    // internals -------------------------------------------------------------------------------------------------------

    private function testOpen()
    {
        if (!$this->file) {
            throw new FileException('File is already closed.');
        }
    }

}
