<?php

namespace Dogma\Http;


/**
 * File download request.
 */
class DownloadRequest extends Request {

    /** @var string */
    private $downloadDir;

    /** @var string */
    private $fileName;

    /** @var string */
    private $fileSuffix;

    /** @var resource */
    private $file;

    
    
    public function __construct($url, $downloadDir) {
        $this->setDownloadDir($downloadDir);
        
        parent::__construct($url);
    }
    
    
    /**
     * @param string
     * @return self
     */
    public function setDownloadDir($dir) {
        if (!is_dir($dir))
            throw new RequestException("Download directory $dir does not exist.");

        $this->downloadDir = rtrim($dir, '/');

        return $this;
    }


    /**
     * @param string
     * @return self
     */
    public function setFileName($name) {
        $this->fileName = $name;

        return $this;
    }


    /**
     * @param string
     * @return self
     */
    public function setFileSuffix($suffix) {
        $this->fileSuffix = $suffix;

        return $this;
    }


    /**
     * Execute request.
     * @param string
     * @param string
     * @return FileResponse
     */
    public function execute($url = NULL, $fileName = NULL) {
        $fileName = $this->prepare($url, $fileName);
        list($response, $error) = $this->sendRequest();
        return $this->createResponse($response, $error, $fileName);
    }


    /**
     * Called by RequestManager.
     * @internal
     *
     * @param string
     * @param string
     * @param bool
     * @return string downloaded file name
     */
    public function prepare($url = NULL, $fileName = NULL) {
        if (is_null($fileName)) $fileName = $this->fileName;
        if (is_null($fileName)) {
            $b = explode('?', $url);
            $b = explode('#', $b[0]);
            $fileName = basename($b[0]);
        }

        $this->file = fopen($this->downloadDir . "/" . $fileName . $this->fileSuffix . ".tmp", 'wb');
        if ($this->file === FALSE)
            throw new RequestException("File $fileName cannot be open!");


        $this->setOption(CURLOPT_FILE, $this->file);
        $this->setOption(CURLOPT_BINARYTRANSFER, TRUE);

        $this->setRequestHeaders();
        if ($url) $this->setOption(CURLOPT_URL, $this->url . $url);

        return $fileName;
    }


    /**
     * Called by RequestManager.
     * @internal
     *
     * @param string|bool
     * @param int
     * @param string
     * @return FileResponse
     */
    public function createResponse($response, $error, $fileName) {
        $info = curl_getinfo($this->curl);
        if ($info === FALSE)
            throw new RequestException("Info cannot be obtained from CURL.");
        
        fclose($this->file);
        unset($this->file);
        return new FileResponse($fileName . $this->fileSuffix, $info, $error);
    }
    
}
