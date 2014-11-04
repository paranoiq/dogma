<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Nette\Utils\ObjectMixin;


/**
 * File Info
 */
class FileInfo extends \SplFileInfo
{

    /* inherits:
    getATime()
    getBasename()
    getCTime()
    getExtension()
    getFileInfo()
    getFilename()
    getGroup()
    getInode()
    getLinkTarget()
    getMTime()
    getOwner()
    getPath()
    getPathInfo()
    getPathname()
    getPerms()
    getRealPath()
    getSize()
    getType()
    isDir()
    isExecutable()
    isFile()
    isLink()
    isReadable()
    isWritable()
    openFile() ***
    setFileClass() ???
    setInfoClass() ok
    __toString() ???
    */

    /**
     * @param string
     */
    /*public function __construct($name)
    {
        try {
            parent::construct($name);
        } catch (\Exception $e) {
            throw new FileException("FileInfo exception", 0, $e);
        }
    }*/

    /**
     * Open the file
     * @param string
     * @param resource
     * @return File
     */
    public function open($mode = File::READ, $streamContext = null)
    {
        return new File($this->getRealPath(), $mode, $streamContext);
    }

    /**
     * @param string
     * @param resource
     * @return \Dogma\Io\File
     */
    public function openFile($mode = File::READ, $streamContext = null)
    {
        return $this->open($mode, $streamContext);
    }

    /**
     * Is current or parent directory
     * @return boolean
     */
    public function isDot()
    {
        return $this->getFilename() === '.' | $this->getFilename() === '..';
    }

    // Nette\Object magic ----------------------------------------------------------------------------------------------

    /**
     * Call to undefined method.
     * @param string
     * @param array
     * @return mixed
     */
    public function __call($name, $args)
    {
        return ObjectMixin::call($this, $name, $args);
    }

    /**
     * Call to undefined static method.
     * @param string
     * @param array
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        ObjectMixin::callStatic(get_called_class(), $name, $args);
    }

    /**
     * Returns property value. Do not call directly.
     * @param string
     * @return mixed
     */
    public function &__get($name)
    {
        return ObjectMixin::get($this, $name);
    }

    /**
     * Sets value of a property. Do not call directly.
     * @param string
     * @param mixed
     * @return void
     */
    public function __set($name, $value)
    {
        ObjectMixin::set($this, $name, $value);
    }

    /**
     * Is property defined?
     * @param string
     * @return bool
     */
    public function __isset($name)
    {
        return ObjectMixin::has($this, $name);
    }

    /**
     * Access to undeclared property.
     * @param string
     */
    public function __unset($name)
    {
        ObjectMixin::remove($this, $name);
    }

}
