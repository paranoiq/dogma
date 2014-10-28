<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;


/**
 * Text file reader/writer
 */
class TextFile extends File
{

    // Line endings:
    const UNIX = "\n";
    const WINDOWS = "\r\n";
    const MAC = "\r";
    const AUTODETECT = null;


    /** @var string */
    public static $internalEncoding = 'utf-8';

    /** @var string */
    protected $encoding = 'utf-8';

    /** @var string */
    protected $nl = self::UNIX;


    /**
     * @param string
     */
    public function setEncoding($encoding)
    {
        $this->encoding = strtolower($encoding);
    }


    /**
     * @param string
     */
    public function setLineEndings($nl)
    {
        $this->nl = $nl;
    }


    /**
     * @return string
     */
    /*public function readChar()
    {
        /// handle multibyte encodings!
        return $this->decode(fgetc($this->file));
    }*/


    /**
     * @param string
     */
    /*public function writeChar($char)
    {
        $this->write($this->encode($char));
    }*/


    /**
     * @return string
     */
    public function readLine()
    {
        ///
        $line = fgets($this->file);

        if ($line === false) {
            if ($this->eof()) {
                throw new FileException('Cannot read data from file. End of file was reached.');
            } else {
                throw new FileException('Cannot read data from file.');
            }
        }
        return $this->decode($line);
    }


    /**
     * @param string
     */
    public function writeLine($line)
    {
        $this->write($this->encode($line) . $this->nl);
    }


    /**
     * @param string
     * @return string
     */
    protected function encode($string)
    {
        if ($this->encoding === self::$internalEncoding) {
            return $string;
        }

        return iconv($this->encoding, self::$internalEncoding, $string);
    }


    /**
     * @param string
     * @return string
     */
    protected function decode($string)
    {
        if ($this->encoding === self::$internalEncoding) {
            return $string;
        }

        return iconv(self::$internalEncoding, $this->encoding, $string);
    }

}
