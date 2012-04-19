<?php

namespace Dogma\Http;

use Nette\Utils\Strings;


class FileResponse extends Response {
    
    /** @var string */
    private $fileName;
    
    
    /**
     * @param string
     * @param array
     * @param int
     */
    public function __construct($fileName, array $info, $error) {
        parent::__construct(NULL, $info, $error);
        
        $this->fileName = $fileName;
    }


    /**
     * @return array
     */
    public function getHeaders() {
        if (!$this->headers) $this->parseFile();

        return $this->headers;
    }


    /**
     * @return string
     */
    public function getBody() {
        if (!$this->headers) $this->parseFile();
        
        return file_get_contents($this->fileName);
    }
    
    
    /**
     * @return string
     */
    public function getFileName() {
        return $this->fileName;
    }
    

    /**
     * Remove headers from downloaded file
     */
    private function parseFile() {
        if (($fp = @fopen($this->fileName . '.tmp', 'rb')) === FALSE) { // internationaly @
            throw new ResponseException("Fopen error for file '$this->fileName.tmp'");
        }

        $headers = Strings::split(@fread($fp, $this->info['header_size']), "~[\n\r]+~", PREG_SPLIT_NO_EMPTY); // internationaly @
        $this->headers = static::parseHeaders($headers);

        @fseek($fp, $this->info['header_size']); // internationaly @

        if (($ft = @fopen($this->fileName, 'wb')) === FALSE) { // internationaly @
            throw new ResponseException("Write error for file '$this->fileName' ");
        }

        while (!feof($fp)) {
            $row = fgets($fp, 4096);
            fwrite($ft, $row);
        }

        @fclose($fp); // internationaly @
        @fclose($ft); // internationaly @

        if (!@unlink($this->fileName . '.tmp')) { // internationaly @
            throw new ResponseException("Error while deleting file $this->fileName.");
        }
        
        chmod($this->fileName, 0755);

        if (!$this->headers) {
            throw new RequestException("Headers parsing failed");
        }
    }
    
    
}
