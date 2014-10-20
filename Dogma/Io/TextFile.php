<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Nette\Diagnostics\Debugger;


/**
 * Text file reader/writer
 */
class TextFile extends File {

    /**#@+ Line endings */
    const UNIX = "\n";
    const WINDOWS = "\r\n";
    const MAC = "\r";
    const AUTODETECT = null;
    /**#@-*/


    /** @var string */
    public static $internalEncoding = 'utf-8';

    /** @var string */
    protected $encoding = 'utf-8';

    /** @var string */
    protected $nl = self::UNIX;


    /**
     * @param $encoding
     * @return self
     */
    public function setEncoding($encoding) {
        $this->encoding = strtolower($encoding);

        return $this;
    }


    /**
     * @param string
     * @return self
     */
    public function setLineEndings($nl) {
        $this->nl = $nl;

        return $this;
    }


    /**
     * @return string
     */
    /*public function readChar() {
        /// handle multibyte encodings!
        return $this->decode(fgetc($this->file));
    }*/


    /**
     * @param string
     * @return self
     */
    /*public function writeChar($char) {
        return $this->write($this->encode($char));
    }*/


    /**
     * @return string
     */
    public function readLine() {
        Debugger::tryError();
        $line = fgets($this->file);

        if (Debugger::catchError($error)) {
            throw new FileException("Cannot read data from file: " . $error->getMessage() . ".", 0, $error);
        } elseif ($line === false) {
            if ($this->eof()) {
                throw new FileException("Cannot read data from file. End of file was reached.");
            } else {
                throw new FileException("Cannot read data from file.");
            }
        }
        return $this->decode($line);
    }


    /**
     * @param string
     * @return self
     */
    public function writeLine($line) {
        return $this->write($this->encode($line) . $this->nl);
    }


    /**
     * @param string
     * @return string
     */
    protected function encode($string) {
        if ($this->encoding === self::$internalEncoding)
            return $string;

        return iconv($this->encoding, self::$internalEncoding, $string);
    }


    /**
     * @param string
     * @return string
     */
    protected function decode($string) {
        if ($this->encoding === self::$internalEncoding)
            return $string;

        return iconv(self::$internalEncoding, $this->encoding, $string);
    }

}
