<?php

namespace Dogma\FileSystem;


/**
 * Text file reader/writer
 */
class TextFile extends File {
    
    const UNIX = "\n";
    const WINDOWS = "\r\n";
    const MAC = "\r";
    const AUTODETECT = NULL;
    
    
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
        $this->encoding = strToLower($encoding);
        
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
    public function readChar() {
        return $this->decode(fgetc($this->file));
    }


    /**
     * @param string
     * @return self
     */
    public function writeChar($char) {
        return $this->write($this->encode($char));
    }


    /**
     * @return string
     */
    public function readLine() {
        return $this->decode(fgets($this->file));
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

