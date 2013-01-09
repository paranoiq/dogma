<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

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
            throw new DirectoryException($e->getMessage(), $e->getCode(), $e);
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
