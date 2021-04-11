<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\ContentType;

use Dogma\Io\ContentTypeDetectionException;
use Dogma\Io\Path;
use Dogma\Language\Encoding;
use Dogma\StrictBehaviorMixin;
use const FILEINFO_EXTENSION;
use const FILEINFO_MIME_ENCODING;
use const FILEINFO_MIME_TYPE;
use const FILEINFO_NONE;
use const FILEINFO_PRESERVE_ATIME;
use function error_clear_last;
use function error_get_last;
use function explode;
use function finfo_buffer;
use function finfo_file;
use function finfo_open;
use function finfo_set_flags;

class ContentTypeDetector
{
    use StrictBehaviorMixin;

    /** @var string */
    private $magicFile;

    /** @var resource|null */
    private $handler;

    public function __construct(string $magicFile)
    {
        $this->magicFile = $magicFile;
    }

    private function init(): void
    {
        error_clear_last();
        /** @var resource|false $handler */
        $handler = finfo_open(FILEINFO_NONE, $this->magicFile);
        if ($handler === false) {
            throw new ContentTypeDetectionException('Cannot initialize fileinfo extension.', error_get_last());
        }
        $this->handler = $handler;
    }

    /**
     * @param string|Path $file
     * @return ContentType|null
     */
    public function detectFileContentType($file): ?ContentType
    {
        if ($this->handler === null) {
            $this->init();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;

        error_clear_last();
        $res = finfo_set_flags($this->handler, FILEINFO_MIME_TYPE | FILEINFO_PRESERVE_ATIME);
        if ($res === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }
        $type = finfo_file($this->handler, $path);
        if ($type === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }

        return ContentType::get($type);
    }

    public function detectStringContentType(string $string): ?ContentType
    {
        if ($this->handler === null) {
            $this->init();
        }

        error_clear_last();
        $res = finfo_set_flags($this->handler, FILEINFO_MIME_TYPE);
        if ($res === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }
        $type = finfo_buffer($this->handler, $string);
        if ($type === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }

        return ContentType::get($type);
    }

    /**
     * @param string|Path $file
     * @return string|null
     */
    public function detectFileExtension($file): ?string
    {
        if ($this->handler === null) {
            $this->init();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;

        error_clear_last();
        $res = finfo_set_flags($this->handler, FILEINFO_EXTENSION | FILEINFO_PRESERVE_ATIME);
        if ($res === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }
        $extensions = finfo_file($this->handler, $path);
        if ($extensions === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }

        $first = explode('/', $extensions)[0];

        return $first === '???' ? null : $first;
    }

    public function detectStringExtension(string $string): ?string
    {
        if ($this->handler === null) {
            $this->init();
        }

        error_clear_last();
        $res = finfo_set_flags($this->handler, FILEINFO_EXTENSION);
        if ($res === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }
        $extensions = finfo_buffer($this->handler, $string);
        if ($extensions === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }

        $first = explode('/', $extensions)[0];

        return $first === '???' ? null : $first;
    }

    /**
     * @param string|Path $file
     * @return Encoding|null
     */
    public function detectFileEncoding($file): ?Encoding
    {
        if ($this->handler === null) {
            $this->init();
        }

        $path = $file instanceof Path ? $file->getPath() : $file;

        error_clear_last();
        $res = finfo_set_flags($this->handler, FILEINFO_MIME_ENCODING | FILEINFO_PRESERVE_ATIME);
        if ($res === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }
        $encoding = finfo_file($this->handler, $path);
        if ($encoding === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }

        return Encoding::get($encoding);
    }

    public function detectStringEncoding(string $string): ?Encoding
    {
        if ($this->handler === null) {
            $this->init();
        }

        error_clear_last();
        $res = finfo_set_flags($this->handler, FILEINFO_MIME_ENCODING);
        if ($res === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }
        $encoding = finfo_buffer($this->handler, $string);
        if ($encoding === false) {
            throw new ContentTypeDetectionException('Cannot read file info.', error_get_last());
        }

        return Encoding::get($encoding);
    }

}
