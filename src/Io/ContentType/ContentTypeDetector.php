<?php declare(strict_types = 1);
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
use Dogma\StrictBehaviorMixin;
use const FILEINFO_MIME_ENCODING;
use const FILEINFO_MIME_TYPE;
use function error_clear_last;
use function error_get_last;
use function finfo_buffer;
use function finfo_file;
use function finfo_open;

class ContentTypeDetector
{
    use StrictBehaviorMixin;

    /** @var string|null */
    private $magicFile;

    /** @var resource|null */
    private $typeHandler;

    /** @var resource|null */
    private $encodingHandler;

    public function __construct(?string $magicFile = null)
    {
        $this->magicFile = $magicFile;
    }

    private function initTypeHandler(): void
    {
        error_clear_last();
        /** @var resource|false $typeHandler */
        $typeHandler = finfo_open(FILEINFO_MIME_TYPE, $this->magicFile);
        if ($typeHandler === false) {
            throw new ContentTypeDetectionException('Cannot initialize finfo extension.', error_get_last());
        }
        $this->typeHandler = $typeHandler;
    }

    private function initEncodingHandler(): void
    {
        error_clear_last();
        /** @var resource|false $encodingHandler */
        $encodingHandler = finfo_open(FILEINFO_MIME_ENCODING, $this->magicFile);
        if ($encodingHandler === false) {
            throw new ContentTypeDetectionException('Cannot initialize finfo extension.', error_get_last());
        }
        $this->encodingHandler = $encodingHandler;
    }

    /**
     * @param string|\Dogma\Io\Path $file
     * @return \Dogma\Io\ContentType\ContentType|null
     */
    public function detectFileContentType($file): ?ContentType
    {
        if ($this->typeHandler === null) {
            $this->initTypeHandler();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;
        $type = finfo_file($this->typeHandler, $path);

        return ContentType::get($type);
    }

    /**
     * @param string|\Dogma\Io\Path $string
     * @return \Dogma\Io\ContentType\ContentType|null
     */
    public function detectStringContentType(string $string): ?ContentType
    {
        if ($this->typeHandler === null) {
            $this->initTypeHandler();
        }

        $type = finfo_buffer($this->typeHandler, $string);

        return ContentType::get($type);
    }

    /**
     * @param string|\Dogma\Io\Path $file
     * @return \Dogma\Language\Encoding|null
     */
    public function detectFileEncoding($file): ?Encoding
    {
        if ($this->encodingHandler === null) {
            $this->initEncodingHandler();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;
        $type = finfo_file($this->encodingHandler, $path);

        return Encoding::get($type);
    }

    /**
     * @param string|\Dogma\Io\Path $string
     * @return \Dogma\Language\Encoding|null
     */
    public function detectStringEncoding(string $string): ?Encoding
    {
        if ($this->encodingHandler === null) {
            $this->initEncodingHandler();
        }

        $type = finfo_buffer($this->encodingHandler, $string);

        return Encoding::get($type);
    }

}
