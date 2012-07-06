<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Nette\Utils\Strings;


class Response extends \Dogma\Object {
    
    /** @var array */
    protected $info;
    
    /** @var ResponseStatus */
    private $status;
    
    /** @var string */
    private $response;
    
    /** @var array */
    protected $headers = array();
    
    /** @var string */
    protected $body;
    
    /** @var mixed Request context */
    protected $context;
    

    /**
     * @param string
     * @param array
     * @param int
     */
    public function __construct($response, ResponseStatus $status, array $info) {
        $this->status = $status;
        $this->info = $info;
        
        if ($response) $this->response = $response;
    }

    
    /**
     * @param mixed Request context
     * @return self
     */
    public function setContext($data) {
        $this->context = $data;
        
        return $this;
    }
    
    
    /**
     * @return mixed
     */
    public function getContext() {
        return $this->context;
    }
    
    
    /**
     * @return bool
     */
    public function isSuccess() {
        return $this->status->isOk();
    }
    
    
    /**
     * @return ResponseStatus
     */
    public function getStatus() {
        return $this->status;
    }
    
    
    /**
     * @return string
     */
    public function getBody() {
        if ($this->response) $this->parseResponse();

        return $this->body;
    }


    /**
     * @return array
     */
    public function getHeaders() {
        if ($this->response) $this->parseResponse();

        return $this->headers;
    }

    
    /**
     * Get all cookies received with this response.
     * @return array
     */
    public function getCookies() {
        if ($this->response) $this->parseResponse();
        
        $cookies = array();
        
        foreach ((array) @$this->headers['Set-Cookie'] as $cookie) {
            $s = explode(';', $cookie);
            list($name, $value) = explode('=', $s[0]);
            $cookies[$name] = $value;
        }
        
        return $cookies;
    }
    
    
    /**
     * @param string|int
     * @return string|array
     */
    public function getInfo($name = NULL) {
        if (is_null($name)) return $this->info;
        
        if (is_int($name)) {
            $tname = CurlHelpers::getCurlInfoName($name);
        } else {
            $tname = $name;
        }
        if (is_null($tname))
            throw new ResponseException("Unknown CURL info '$name'!");
        
        return $this->info[$tname];
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
        return (string) $this->getBody();
    }
    
    
    // internals -------------------------------------------------------------------------------------------------------
    

    /**
     * Remove headers from response.
     */
    private function parseResponse() {
        $headers = Strings::split(substr($this->response, 0, $this->info['header_size']), "~[\n\r]+~", PREG_SPLIT_NO_EMPTY);
        $this->headers = static::parseHeaders($headers);
        $this->body = substr($this->response, $this->info['header_size']);
        $this->response = NULL;
    }


    /**
     * Parses headers from given list
     * @param array
     * @return array
     */
    public static function parseHeaders($headers) {
        $found = array();

        // extract version and status
        $version_and_status = array_shift($headers);
        $m = Strings::match($version_and_status, '~HTTP/(?P<version>\d\.\d)\s(?P<code>\d\d\d)\s(?P<status>.*)~');
        if (count($m) > 0) {
            $found['Http-Version'] = $m['version'];
            $found['Status-Code'] = $m['code'];
            $found['Status'] = $m['code'] . ' ' . $m['status'];
        }

        // convert headers to associative array
        foreach ($headers as $header) {
            $m = Strings::match($header, '~(?P<header>.*?)\:\s(?P<value>.*)~');
            if (isset($found[$m['header']])) {
                if (is_array($found[$m['header']])) {
                    $found[$m['header']][] = $m['value'];
                } else {
                    $found[$m['header']] = array($found[$m['header']]);
                    $found[$m['header']][] = $m['value'];
                }
            } else {
                $found[$m['header']] = $m['value'];
            }
        }

        return $found;
    }
    
}
