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
class Attachment extends \Dogma\Object
{

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
     * @param string
     * @param string
     * @param string
     * @param string[]
     */
    public function __construct($data, $headers = [])
    {
        if ($data instanceof File) {
            $this->file = $data;
        } else {
            $this->data = $data;
        }
        $this->headers = $headers;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return @$this->headers['disposition-filename']; // not on 'inline'
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->headers['content-type'];
    }

    /**
     * @return string
     */
    public function getCharset()
    {
        return @$this->headers['content-charset']; // not on binary
    }

    /**
     * @return string
     */
    public function getDisposition()
    {
        return $this->headers['content-disposition'];
    }

    /**
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return integer
     */
    public function getLength()
    {
        if ($this->data) {
            return strlen($this->data);
        } else {
            return $this->file->getSize();
        }
    }

    /**
     * Get file content.
     * @return string
     */
    public function getContent()
    {
        if ($this->data) {
            return $this->data;
        } else {
            return $this->file->getContent();
        }
    }

    /**
     * Get File object.
     * @return \Dogma\Io\File
     */
    public function getFile()
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
