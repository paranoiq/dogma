<?php

namespace Dogma\FileSystem;

use FilesystemIterator;
use Dogma;


/**
 * Directory iterator
 */
class DirectoryIterator extends FilesystemIterator {
    
    
    private $flags;


    /**
     * @param string
     * @param int
     */
    public function __construct($path, $flags = NULL) {
        if (isset($flags))
            $flags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;
        
        $this->flags = $flags;
        try {
            if ($flags & FilesystemIterator::CURRENT_AS_FILEINFO) {
                parent::__construct($path, $flags | FilesystemIterator::CURRENT_AS_PATHNAME);
            } else {
                parent::__construct($path, $flags);
            }
        } catch (\UnexpectedValueException $e) {
            throw new Dogma\DirectoryException($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * @param int
     */
    public function setFlags($flags) {
        $this->flags = $flags;
        if ($flags & FilesystemIterator::CURRENT_AS_FILEINFO) {
            parent::setFlags($flags | FilesystemIterator::CURRENT_AS_PATHNAME);
        } else {
            parent::setFlags($flags);
        }
    }


    /**
     * @return FileInfo|mixed
     */
    public function current() {
        if ($this->flags & FilesystemIterator::CURRENT_AS_FILEINFO) {
            return new FileInfo(parent::current());
        }
        return parent::current();
    }
    
}

