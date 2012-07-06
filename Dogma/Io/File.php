<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\FileSystem;

use Dogma\Io;


/**
 * File reader/writer
 */
class File extends \Nette\Object {
    
    /**#@+ File opening mode */
    // if not found: ERROR; keep content
    const READ = 'rb';
    const READ_WRITE = 'r+b';
    // if not found: create; truncate content
    const TRUNCATE_WRITE = 'wb';
    const TRUNCATE_READ_WRITE = 'w+b';
    // if not found: create; keep content, point to end of file
    const APPEND_WRITE = 'ab';
    const APPEND_READ_WRITE = 'a+b';
    // if found: ERROR; no content
    const CREATE_WRITE = 'xb';
    const CREATE_READ_WRITE = 'x+b';
    // if not found: create; keep content
    const OPEN_CREATE_WRITE = 'cb';
    const OPEN_CREATE_READ_WRITE = 'c+b';
    /**#@-*/
    
    
    /**#@+ Position from */
    const BEGINNING = 0;
    const CURRENT = 1;
    const END = 2;
    /**#@-*/
    
    
    /**#@+ Lock type */
    const SHARED = 1;
    const EXCLUSIVE = 2;
    const UNLOCK = 3;
    const NON_BLOCKING = 4;
    /**#@-*/
    
    
    
    /** @var string file name */
    protected $name;
    
    /** @var string file mode */
    protected $mode;
    
    /** @var resource */
    protected $streamContext;
    
    /** @var resource file descriptor */
    protected $file;
    
    /** @var array */
    private $stat;
    
    
    /**
     * Open file
     * @param string file name
     * @param string
     * @param resource
     */
    public function __construct($name, $mode = self::READ, $streamContext = NULL) {
        if ($name === NULL) return;
        
        $this->name = (string) $name;
        
        $this->mode = $mode;
        
        if ($streamContext) {
            $this->streamContext = $streamContext;
            $this->file = fopen($this->name, $mode, FALSE, $streamContext);
        } else {
            $this->file = fopen($this->name, $mode, FALSE);
        }
        
        if (!$this->file) {
            /// zjistit důvod
            throw new FileException("Cannot open file in mode '$mode'.");
        }
    }
    
    
    /**
     * Close file
     * @return self
     */
    public function close() {
        $this->testOpen();
        if (fclose($this->file)) {
            throw new FileException("Cannot close file.");
        }
        $this->stat = NULL;
        return $this;
    }
    
    
    /**
     * End of file reached?
     * @return bool
     */
    public function eof() {
        $this->testOpen();
        return feof($this->file);
    }
    
    
    /**
     * Read binary data from file
     * @param int maximal length of input
     * @return string
     */
    public function read($length) {
        $this->testOpen();
        $data = fread($this->file, $length);
        if ($data === FALSE) {
            if ($this->eof()) {
                throw new FileException("Cannot read data from file. End of file was reached.");
            } else {
                throw new FileException("Cannot read data from file.");
            }
        }
        return $data;
    }
    
    
    /**
     * Write binary data to file
     * @param string
     * @return self
     */
    public function write($data) {
        $this->testOpen();
        if (fwrite($this->file, $data) === FALSE) {
            throw new FileException("Cannot write data to file.");
        }
        return $this;
    }
    
    
    /**
     * Truncate file.
     * @param int $size new file size in bytes
     * @return self
     */
    public function truncate($size) {
        $this->testOpen();
        if (ftruncate($this->file, $size) === FALSE) {
            throw new FileException("Cannot truncate file.");
        }
        return $this;
    }
    
    
    /**
     * Flush the file output buffer (fsync)
     * @return self
     */
    public function flush() {
        $this->testOpen();
        if (fflush($this->file) === FALSE) {
            throw new FileException("Cannot truncate file.");
        }
        $this->stat = NULL;
        return $this;
    }
    
    
    /**
     * Lock file. see PHP flock() documentation
     * @param int $mode locking mode
     * @param int $wouldBlock would block (in non blocking mode)
     * @return self
     */
    public function lock($mode = self::SHARED, &$wouldBlock = NULL) {
        $this->testOpen();
        $wb = NULL;
        if (flock($this->file, $mode, $wb) === FALSE) {
            if ($wb) {
                $wouldBlock = $wb;
                throw new FileException("Non-blocking lock cannot be acquired. ");
            } else {
                throw new FileException("Cannot lock file.");
            }
        }
        return $this;
    }
    
    
    /**
     * Release file lock
     * @return self
     */
    public function unlock() {
        $this->testOpen();
        if (flock($this->file, LOCK_UN) === FALSE) {
            throw new FileException("Cannot unlock file.");
        }
        return $this;
    }


    /**
     * Set the file pointer position
     * @param int|bool position in bytes or TRUE for end of file
     * @param int $from
     * @return self
     */
    public function setPosition($position, $from = self::BEGINNING) {
        $this->testOpen();
        if ($position === TRUE && fseek($this->file, 0, SEEK_END) !== 0) {
            throw new FileException("Cannot set file pointer position.");
        }
        if (fseek($this->file, $position, $from) !== 0) {
            throw new FileException("Cannot set file pointer position.");
        }
        return $this;
    }
    
    
    /**
     * Get file pointer position
     * @return int
     */
    public function getPosition() {
        $this->testOpen();
        $pos = ftell($this->file);
        if ($pos === FALSE) {
            throw new FileException("Cannot get file pointer position.");
        }
        return $pos;
    }
    
    
    /**
     * Get file name
     * @return string
     */
    public function getName() {
        $this->getInfo()->getFilename();
    }
    
    
    /**
     * Get file info
     * @return FileInfo
     */
    public function getInfo() {
        return new FileInfo($this->name);
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
    
    
    public function getResponseContext() {
        ///
    }
    
    
    /*Array(
        [wrapper_data] => Array
            (
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
            )
    
        [wrapper_type] => http
        [stream_type] => tcp_socket/ssl
        [mode] => r
        [unread_bytes] => 438
        [seekable] => 
        [uri] => http://www.example.com/
        [timed_out] => 
        [blocked] => 1
        [eof] => 
    )*/
    
    
    // stat ------------------------------------------------------------------------------------------------------------
    
    
    private function loadStat() {
        if (!$this->stat) {
            if ($this->file) {
                if (!$this->stat = @fstat($this->file)) {
                    throw new FileException("Cannot acquire file metadata.");
                }
            } else {
                if (!$this->stat = @stat($this->name)) {
                    throw new FileException("Cannot acquire file metadata.");
                }
            }
        }
    }
    
    
    public function getDeviceId() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['dev'];
    }
    
    public function getInode() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['ino'];
    }
    
    public function getPerms() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['mode'];
    }
    
    public function getLinksCount() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['nlinks'];
    }
    
    public function getOwner() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['uid'];
    }
    
    public function getGroup() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['gid'];
    }
    
    public function getDeviceType() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['rdev'];
    }
    
    public function getSize() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['size'];
    }
    
    public function getATime() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['atime'];
    }
    
    public function getMTime() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['mtime'];
    }
    
    public function getCTime() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['ctime'];
    }
    
    public function getBlockSize() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['blksize'];
    }
    
    public function getBlocks() {
        if (!$this->stat) $this->loadStat();
        return $this->stat['blocks'];
    }
    
    
    // factories -------------------------------------------------------------------------------------------------------


    /**
     * @return self
     */
    public static function createTemporaryFile() {
        $fd = tmpfile();
        if (!$fd) {
            throw new FileException("Cannot create a temporary file.");
        }
        $file = new static(NULL);
        $file->file = $fd;
        return $file;
    }
    
    
    // internals -------------------------------------------------------------------------------------------------------
    
    
    private function testOpen() {
        if (!$this->file)
            throw new FileException("File is already closed.");
    }
    
}

