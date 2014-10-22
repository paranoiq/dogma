<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Nette\Diagnostics\Debugger;
use Nette\Callback;


/**
 * Binary file reader/writer
 *
 * @property FileStat $info
 */
class File extends \Nette\Object {

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
     * @var int Set this *same or greater* then the alocation unit of your storage (disk sector, RAID strip etc.)
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
     * @param string file name or stream resource
     * @param string
     * @param resource
     */
    public function __construct($file, $mode = self::READ_WRITE, $streamContext = null) {
        if ($file === null) return;

        if (is_resource($file) && get_resource_type($file) === 'stream') {
            $this->file = $file;
            return;
        }

        $this->name = (string) $file;

        Debugger::tryError();
        if ($streamContext) {
            $this->streamContext = $streamContext;
            $this->file = fopen($this->name, $mode, false, $streamContext);
        } else {
            $this->file = fopen($this->name, $mode, false);
        }

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot open file in mode '$mode': " . $error->getMessage() . ".", 0, $error);
        }
    }


    public function __destruct() {
        if ($this->file) fclose($this->file);
    }


    /**
     * @return boolean
     */
    public function isOpen() {
        return (bool) $this->file;
    }


    /**
     * Close file
     */
    public function close() {
        $this->testOpen();

        Debugger::tryError();
        $res = fclose($this->file);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot close file: " . $error->getMessage() . ".", 0, $error);
        } elseif (!$res) {
            throw new FileException("Cannot close file.");
        }
        $this->stat = null;
        $this->file = null;
    }


    /**
     * End of file reached?
     * @return boolean
     */
    public function eof() {
        $this->testOpen();

        Debugger::tryError();
        $feof = feof($this->file);

        if (Debugger::catchError($error)) {
            throw new FileException("Error whn checking End Of File: " . $error->getMessage() . ".", 0, $error);
        }

        return $feof;
    }


    /**
     * Read binary data from file
     * @param integer $length maximal length of input
     * @return string
     */
    public function read($length = null) {
        if (empty($length)) $length = self::$defaultChunkSize;
        $this->testOpen();

        Debugger::tryError();
        $data = fread($this->file, $length);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot read data from file: " . $error->getMessage() . ".", 0, $error);
        } elseif ($data === false) {
            if ($this->eof()) {
                throw new FileException("Cannot read data from file. End of file was reached.");
            } else {
                throw new FileException("Cannot read data from file.");
            }
        }

        return $data;
    }


    /**
     * Copy range of data to another File or callback
     * @param \Dogma\Io\File|callable
     * @param integer
     * @param integer
     * @return integer actual length of copied data
     */
    public function copyData($destination, $start = null, $length = 0, $chunkSize = null) {
        if (empty($chunkSize)) $chunkSize = self::$defaultChunkSize;
        if (!empty($start)) $this->setPosition($start);

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
                throw new \InvalidArgumentException("Destination must be File or callable!");
            }
        }

        return $done;
    }


    /**
     * Get entire content of file. Beware of BIG files!
     * @return string
     */
    public function getContent() {
        if ($this->getPosition()) $this->setPosition(0); // ?

        $str = "";
        while (!$this->eof()) {
            $str .= $this->read();
        }

        return $str;
    }


    /**
     * Write binary data to file
     * @param string
     */
    public function write($data) {
        $this->testOpen();

        Debugger::tryError();
        $res = fwrite($this->file, $data);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot write data to file: " . $error->getMessage() . ".", 0, $error);
        } elseif ($res === false) {
            throw new FileException("Cannot write data to file.");
        }
    }


    /**
     * Truncate file and move pointer at the end
     * @param integer $size new file size in bytes
     */
    public function truncate($size = 0) {
        $this->testOpen();

        Debugger::tryError();
        $res = ftruncate($this->file, $size);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot truncate file: " . $error->getMessage() . ".", 0, $error);
        } elseif ($res === false) {
            throw new FileException("Cannot truncate file.");
        }

        $this->setPosition($size);
    }


    /**
     * Flush the file output buffer (fsync)
     */
    public function flush() {
        $this->testOpen();

        Debugger::tryError();
        $res = fflush($this->file);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot flush file cache: " . $error->getMessage() . ".", 0, $error);
        } elseif ($res === false) {
            throw new FileException("Cannot flush file cache.");
        }
        $this->stat = null;
    }


    /**
     * Lock file. see PHP flock() documentation
     * @param integer $mode locking mode
     * @param integer $wouldBlock would block (in non blocking mode)
     */
    public function lock($mode = self::SHARED, &$wouldBlock = null) {
        $this->testOpen();

        $wb = null;
        Debugger::tryError();
        $res = flock($this->file, $mode, $wb);

        if (Debugger::catchError($error)) {
            if ($wb) {
                throw new FileException("Non-blocking lock cannot be acquired: " . $error->getMessage() . ".", 0, $error);
            } else {
                throw new FileException("Cannot lock file: $error->message.", 0, $error);
            }
        } elseif ($res === false) {
            if ($wb) {
                $wouldBlock = $wb;
                throw new FileException("Non-blocking lock cannot be acquired.");
            } else {
                throw new FileException("Cannot lock file.");
            }
        }
    }


    /**
     * Release file lock
     */
    public function unlock() {
        $this->testOpen();

        Debugger::tryError();
        $res = flock($this->file, LOCK_UN);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot unlock file: " . $error->getMessage() . ".", 0, $error);
        } elseif ($res === false) {
            throw new FileException("Cannot unlock file.");
        }
    }


    /**
     * Set the file pointer position
     * @param integer|boolean position in bytes or true for end of file
     * @param integer $from
     */
    public function setPosition($position, $from = self::BEGINNING) {
        $this->testOpen();

        if ($position === true) {
            $position = 0;
            $from = SEEK_END;
        }

        Debugger::tryError();
        $res = fseek($this->file, $position, $from);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot set file pointer position: " . $error->getMessage() . ".", 0, $error);
        } elseif ($res !== 0) {
            throw new FileException("Cannot set file pointer position.");
        }
    }


    /**
     * Get file pointer position
     * @return integer
     */
    public function getPosition() {
        $this->testOpen();

        Debugger::tryError();
        $pos = ftell($this->file);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot get file pointer position: " . $error->getMessage() . ".", 0, $error);
        } elseif ($pos === false) {
            throw new FileException("Cannot get file pointer position.");
        }

        return $pos;
    }


    /**
     * Get file name
     * @return string
     */
    public function getName() {
        if (empty($this->name)) {
            $meta = $this->getMetaData();
            $this->name = $meta['uri'];
        }

        return $this->name;
    }


    /**
     * Get file info
     * @return \Dogma\Io\FileStat
     */
    public function getInfo() {
        if (!$this->stat) {
            if ($this->file) {
                if (!$stat = @fstat($this->file)) {
                    throw new FileException("Cannot acquire file metadata.");
                }
            } else {
                if (empty($this->name)) $this->getName();

                if (!$stat = @stat($this->name)) {
                    throw new FileException("Cannot acquire file metadata.");
                }
            }
            $this->stat = new FileStat($stat);
        }

        return $this->stat;
    }


    /**
     * Get stream meta data for files opened via HTTP, FTP…
     * @return array
     */
    public function getMetaData() {
        return stream_get_meta_data($this->file);
    }


    /**
     * Get stream wraper headers (HTTP)
     * @return array
     */
    public function getWraperData() {
        $data = stream_get_meta_data($this->file);

        return $data['wraper_data'];
    }


    //public function getResponseContext() {
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


    /**
     * @return self
     */
    public static function createTemporaryFile() {
        Debugger::tryError();
        $fd = tmpfile();

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot create a temporary file: " . $error->getMessage() . ".", 0, $error);
        } elseif (!$fd) {
            throw new FileException("Cannot create a temporary file.");
        }
        $file = new static(null, 'w+');
        $file->file = $fd;

        return $file;
    }


    // internals -------------------------------------------------------------------------------------------------------


    private function testOpen() {
        if (!$this->file)
            throw new FileException("File is already closed.");
    }

}
