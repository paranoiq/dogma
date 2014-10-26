<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Nette;


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
        return Nette\ObjectMixin::call($this, $name, $args);
    }


    /**
     * Call to undefined static method.
     * @param string
     * @param array
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        return Nette\ObjectMixin::callStatic(get_called_class(), $name, $args);
    }


    /**
     * Adding method to class.
     * @param string
     * @param mixed
     * @return mixed
     */
    public static function extensionMethod($name, $callback = null)
    {
        if (strpos($name, '::') === false) {
            $class = get_called_class();
        } else {
            list($class, $name) = explode('::', $name);
        }
        $class = new Nette\Reflection\ClassType($class);
        if ($callback === null) {
            return $class->getExtensionMethod($name);
        } else {
            $class->setExtensionMethod($name, $callback);
        }
    }


    /**
     * Returns property value. Do not call directly.
     * @param string
     * @return mixed
     */
    public function &__get($name)
    {
        return Nette\ObjectMixin::get($this, $name);
    }


    /**
     * Sets value of a property. Do not call directly.
     * @param string
     * @param mixed
     * @return void
     */
    public function __set($name, $value)
    {
        Nette\ObjectMixin::set($this, $name, $value);
    }


    /**
     * Is property defined?
     * @param string
     * @return bool
     */
    public function __isset($name)
    {
        return Nette\ObjectMixin::has($this, $name);
    }


    /**
     * Access to undeclared property.
     * @param string
     */
    public function __unset($name)
    {
        Nette\ObjectMixin::remove($this, $name);
    }

}
