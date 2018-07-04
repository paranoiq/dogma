<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Filesystem;

use Dogma\Io\File;
use Dogma\Io\FileMode;
use Dogma\Io\Path;
use Dogma\StrictBehaviorMixin;
use function basename;
use function str_replace;

/**
 * File Info
 */
class FileInfo extends \SplFileInfo implements Path
{
    use StrictBehaviorMixin;

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

    public function getPath(): string
    {
        return str_replace('\\', '/', parent::getPath());
    }

    public function getName(): string
    {
        return basename($this->getPath());
    }

    /**
     * @param string $mode
     * @param resource|null $streamContext
     * @return \Dogma\Io\File
     */
    public function open(string $mode = FileMode::OPEN_READ, $streamContext = null): File
    {
        return new File($this->getRealPath(), $mode, $streamContext);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $mode
     * @param string|null $includePath
     * @param resource|null $streamContext
     * @return \Dogma\Io\File
     */
    public function openFile($mode = FileMode::OPEN_READ, $includePath = null, $streamContext = null): File
    {
        /// include path!
        return $this->open($mode, $streamContext);
    }

    /**
     * Is current or parent directory
     */
    public function isDot(): bool
    {
        return $this->getFilename() === '.' || $this->getFilename() === '..';
    }

}
