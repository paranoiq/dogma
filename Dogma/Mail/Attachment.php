<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mail;

use Dogma\Io\File;


/**
 * Email Attachment
 *
 * @property-read $fileName
 * @property-read $contentType
 * @property-read $charset
 * @property-read $disposition
 * @property-read $length
 */
class Attachment
{
    use \Dogma\StrictBehaviorMixin;

    /** Content disposition */
    const ATTACHMENT = 'attachment';
    const INLINE = 'inline';


    /** @var string */
    private $data;

    /** @var \Dogma\Io\File */
    private $file;

    /** @var string[] */
    private $headers;

    /**
     * @param \Dogma\Io\File|string
     * @param string[]
     */
    public function __construct($data, array $headers = [])
    {
        if ($data instanceof File) {
            $this->file = $data;
        } else {
            $this->data = $data;
        }
        $this->headers = $headers;
    }

    public function getFileName(): string
    {
        return @$this->headers['disposition-filename']; // not on 'inline'
    }

    public function getContentType(): string
    {
        return $this->headers['content-type'];
    }

    public function getCharset(): string
    {
        return @$this->headers['content-charset']; // not on binary
    }

    public function getDisposition(): string
    {
        return $this->headers['content-disposition'];
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function getLength(): int
    {
        if ($this->data) {
            return strlen($this->data);
        } else {
            return $this->file->getSize();
        }
    }

    /**
     * Get file content.
     */
    public function getContent(): string
    {
        if ($this->data) {
            return $this->data;
        } else {
            return $this->file->getContent();
        }
    }

    /**
     * Get File object.
     */
    public function getFile(): File
    {
        if ($this->file) {
            return $this->file;
        } else {
            $this->file = File::createTemporaryFile();
            $this->file->write($this->data);
            return $this->file;
        }
    }

}
