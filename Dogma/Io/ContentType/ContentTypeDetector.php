<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\ContentType;

use Dogma\Io\Path;
use Dogma\Language\Encoding;

class ContentTypeDetector
{

    /** @var string */
    private $magicFile;

    /** @var resource */
    private $typeHandler;

    /** @var resource */
    private $encodingHandler;

    public function __construct(string $magicFile = null)
    {
        $this->magicFile = $magicFile;
    }

    private function initTypeHandler()
    {
        error_clear_last();
        $typeHandler = finfo_open(FILEINFO_MIME_TYPE, $this->magicFile);
        if ($typeHandler === false) {
            throw new \Dogma\Io\ContentType\ContentTypeDetectionException('Cannot initialize finfo extension.', error_get_last());
        }
        $this->typeHandler = $typeHandler;
    }

    private function initEncodingHandler()
    {
        error_clear_last();
        $encodingHandler = finfo_open(FILEINFO_MIME_ENCODING, $this->magicFile);
        if ($encodingHandler === false) {
            throw new \Dogma\Io\ContentType\ContentTypeDetectionException('Cannot initialize finfo extension.', error_get_last());
        }
        $this->encodingHandler = $encodingHandler;
    }

    /**
     * @param string|\Dogma\Io\Path
     * @return \Dogma\Io\ContentType\ContentType|null
     */
    public function detectFileContentType($file)
    {
        if ($this->typeHandler === null) {
            $this->initTypeHandler();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;
        $type = finfo_file($this->typeHandler, $path);

        return ContentType::get($type);
    }

    /**
     * @param string|\Dogma\Io\Path
     * @return \Dogma\Io\ContentType\ContentType|null
     */
    public function detectStringContentType(string $string)
    {
        if ($this->typeHandler === null) {
            $this->initTypeHandler();
        }

        $type = finfo_buffer($this->typeHandler, $string);

        return ContentType::get($type);
    }

    /**
     * @param string|\Dogma\Io\Path
     * @return \Dogma\Language\Encoding|null
     */
    public function detectFileEncoding($file)
    {
        if ($this->encodingHandler === null) {
            $this->initEncodingHandler();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;
        $type = finfo_file($this->encodingHandler, $path);

        return Encoding::get($type);
    }

    /**
     * @param string|\Dogma\Io\Path
     * @return \Dogma\Language\Encoding|null
     */
    public function detectStringEncoding(string $string)
    {
        if ($this->encodingHandler === null) {
            $this->initEncodingHandler();
        }

        $type = finfo_buffer($this->encodingHandler, $string);

        return Encoding::get($type);
    }

}
