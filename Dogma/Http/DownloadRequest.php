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


    /**
     * @param string $url
     * @param string $downloadDir
     */
    public function __construct($url, $downloadDir) {
        parent::__construct($url);

        $this->setDownloadDir($downloadDir);
    }


    /**
     * @param string
     */
    public function setDownloadDir($dir) {
        if (!is_dir($dir))
            throw new RequestException("Download directory $dir does not exist.");

        $this->downloadDir = rtrim($dir, '/');
    }


    /**
     * @param string
     */
    public function setFileName($name) {
        $this->fileName = $name;
    }


    /**
     * @param string
     */
    public function setFileSuffix($suffix) {
        $this->fileSuffix = $suffix;
    }


    // output handling -------------------------------------------------------------------------------------------------


    /**
     * Execute request.
     * @param string
     * @param string
     * @return \Dogma\Http\FileResponse
     */
    public function execute($urlSuffix = null, $fileName = null) {
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
     * @param boolean
     * @return string downloaded file name
     */
    public function prepare($urlSuffix = null, $fileName = null) {
        parent::prepare($urlSuffix);

        if (is_null($fileName)) $fileName = $this->fileName;
        if (is_null($fileName)) {
            $b = explode('?', $urlSuffix);
            $b = explode('#', $b[0]);
            $fileName = basename($b[0]);
        }

        $this->file = fopen($this->downloadDir . "/" . $fileName . $this->fileSuffix . ".tmp", 'wb');
        if ($this->file === false)
            throw new RequestException("File $fileName cannot be open!");


        $this->setOption(CURLOPT_FILE, $this->file);
        $this->setOption(CURLOPT_BINARYTRANSFER, true);

        return $fileName;
    }


    /**
     * Called by RequestManager.
     * @internal
     *
     * @param string|bool
     * @param integer
     * @param string
     * @return \Dogma\Http\FileResponse
     */
    public function createResponse($response, $error, $fileName) {
        $info = curl_getinfo($this->curl);
        if ($info === false)
            throw new RequestException("Info cannot be obtained from CURL.");

        fclose($this->file);
        unset($this->file);
        return new FileResponse($fileName . $this->fileSuffix, $info, $error);
    }

}
