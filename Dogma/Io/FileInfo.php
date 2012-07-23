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
class FileInfo extends \SplFileInfo {
    
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
    /*public function __construct($name) {
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
    public function open($mode = File::READ, $streamContext = NULL) {
        return new File($this->getRealPath(), $mode, $streamContext);
    }


    /**
     * @param string
     * @param resource
     * @return File
     */
    public function openFile($mode = File::READ, $streamContext = NULL) {
        return $this->open($mode, $streamContext);
    }


    /**
     * Is current or parent directory
     * @return bool
     */
    public function isDot() {
        return $this->getFilename() === '.' | $this->getFilename() === '..';
    }
    
    
    // Nette\Object magic ----------------------------------------------------------------------------------------------
    
    
    /**
     * Call to undefined method.
     * @param  string  method name
     * @param  array   arguments
     * @return mixed
     */
    public function __call($name, $args) {
        return Nette\ObjectMixin::call($this, $name, $args);
    }
    
    
    /**
     * Call to undefined static method.
     * @param  string  method name (in lower case!)
     * @param  array   arguments
     * @return mixed
     */
    public static function __callStatic($name, $args) {
        return Nette\ObjectMixin::callStatic(get_called_class(), $name, $args);
    }
    
    
    /**
     * Adding method to class.
     * @param  string  method name
     * @param  mixed   callback or closure
     * @return mixed
     */
    public static function extensionMethod($name, $callback = NULL) {
        if (strpos($name, '::') === FALSE) {
            $class = get_called_class();
        } else {
            list($class, $name) = explode('::', $name);
        }
        $class = new Nette\Reflection\ClassType($class);
        if ($callback === NULL) {
            return $class->getExtensionMethod($name);
        } else {
            $class->setExtensionMethod($name, $callback);
        }
    }
    
    
    /**
     * Returns property value. Do not call directly.
     * @param  string  property name
     * @return mixed   property value
     */
    public function &__get($name) {
        return Nette\ObjectMixin::get($this, $name);
    }
    
    
    /**
     * Sets value of a property. Do not call directly.
     * @param  string  property name
     * @param  mixed   property value
     * @return void
     */
    public function __set($name, $value) {
        Nette\ObjectMixin::set($this, $name, $value);
    }
    
    
    /**
     * Is property defined?
     * @param  string  property name
     * @return bool
     */
    public function __isset($name) {
        return Nette\ObjectMixin::has($this, $name);
    }
    
    
    /**
     * Access to undeclared property.
     * @param  string  property name
     * @return void
     * @throws MemberAccessException
     */
    public function __unset($name) {
        Nette\ObjectMixin::remove($this, $name);
    }
    
}

