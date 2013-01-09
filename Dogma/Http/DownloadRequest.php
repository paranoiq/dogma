<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

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
        parent::__construct($url);

        $this->setDownloadDir($downloadDir);
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


    // output handling -------------------------------------------------------------------------------------------------


    /**
     * Execute request.
     * @param string
     * @param string
     * @return FileResponse
     */
    public function execute($urlSuffix = NULL, $fileName = NULL) {
        $fileName = $this->prepare($urlSuffix, $fileName);
        $response = curl_exec($this->curl);
        $error = curl_errno($this->curl);
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
    public function prepare($urlSuffix = NULL, $fileName = NULL) {
        parent::prepare($urlSuffix);

        if (is_null($fileName)) $fileName = $this->fileName;
        if (is_null($fileName)) {
            $b = explode('?', $urlSuffix);
            $b = explode('#', $b[0]);
            $fileName = basename($b[0]);
        }

        $this->file = fopen($this->downloadDir . "/" . $fileName . $this->fileSuffix . ".tmp", 'wb');
        if ($this->file === FALSE)
            throw new RequestException("File $fileName cannot be open!");


        $this->setOption(CURLOPT_FILE, $this->file);
        $this->setOption(CURLOPT_BINARYTRANSFER, TRUE);

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
