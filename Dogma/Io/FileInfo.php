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
        } catch (\Throwable $e) {
            throw new FileException("FileInfo exception", 0, $e);
        }
    }*/

    /**
     * Open the file
     * @param string
     * @param resource
     * @return File
     */
    public function open(string $mode = File::READ, $streamContext = null): File
    {
        return new File($this->getRealPath(), $mode, $streamContext);
    }

    /**
     * @param string
     * @param string
     * @param resource
     * @return \Dogma\Io\File
     */
    public function openFile($mode = File::READ, $includePath = null, $streamContext = null): File // compat
    {
        /// include path!
        return $this->open($mode, $streamContext);
    }

    /**
     * Is current or parent directory
     */
    public function isDot(): bool
    {
        return $this->getFilename() === '.' | $this->getFilename() === '..';
    }

    // Nette\Object magic ----------------------------------------------------------------------------------------------

    /**
     * Call to undefined method.
     * @param string
     * @param mixed[]
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return ObjectMixin::call($this, $name, $args);
    }

    /**
     * Call to undefined static method.
     * @param string
     * @param mixed[]
     * @return mixed
     */
    public static function __callStatic(string $name, array $args)
    {
        ObjectMixin::callStatic(get_called_class(), $name, $args);
    }

    /**
     * Returns property value. Do not call directly.
     * @param string
     * @return mixed
     */
    public function &__get(string $name)
    {
        return ObjectMixin::get($this, $name);
    }

    /**
     * Sets value of a property. Do not call directly.
     * @param string
     * @param mixed
     */
    public function __set(string $name, $value)
    {
        ObjectMixin::set($this, $name, $value);
    }

    /**
     * Is property defined?
     */
    public function __isset(string $name): bool
    {
        return ObjectMixin::has($this, $name);
    }

    /**
     * Access to undeclared property.
     */
    public function __unset(string $name)
    {
        ObjectMixin::remove($this, $name);
    }

}
