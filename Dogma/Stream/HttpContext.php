<?php

namespace Dogma\Stream;


class HttpContext extends StreamContext implements \ArrayAccess {
    
    
    private $method = 'get';
    
    /** @var array */
    private $headers = array();
    
    /** @var array */
    private $cookies = array();
    
    private $postData = array();
    
    private $postFiles = array();
    
    
    /**
     * @param array array(name=>value)
     * @param callback stream notification callback
     */
    public function __construct($options = array(), $callback = NULL) {
        $params = array();
        if ($callback) $params['notification'] = $callback; ///
        
        $this->context = stream_context_create(array('http' => $options), $params);
    }
    
    
    
    /**
     * @return resource stream context
     */
    public function getContext() {
        $this->setOption('http', 'method', $this->method);
        $this->compileContent();
        $this->compileHeaders();
        return parent::getContext();
    }
    
    
    /**
     * @param string
     * @return HttpContext
     */
    public function setMethod($method) {
        $this->method = strtolower($method);
        return $this;
    }
    
    /**
     * @param int timeout in seconds
     */
    public function setTimeout($timeout) {
        return $this->setOption('http', 'timeout', $timeout);
    }
    
    
    // proxy, request_fulluri
    
    
    // headers ---------------------------------------------------------------------------------------------------------
    
    
    /**
     * @param string
     * @param string
     * @return StreamContext
     */
    public function addCookie($name, $value) {
        $this->cookies[$name] = $value;
        return $this;
    }
    
    
    /**
     * @param string
     * @param mixed
     * @return StreamContext
     */
    public function addHeader($name, $value) {
        $this->headers[$name] = $value;
        return $this;
    }
    
    
    /**
     * @param array
     * @return StreamContext
     */
    public function setHeaders($headers) {
        $this->headers = $headers;
        return $this;
    }
    
    
    private function compileHeaders() {
        $headers = '';
        foreach ($this->headers as $name => $value) {
            $headers .= "$name: $value\r\n";
        }
        $headers .= $this->compileCookies();
        $this->setOption('http', 'header', $headers);
    }
    
    
    // http://www.faqs.org/rfcs/rfc2109.html
    private function compileCookies() {
        if (!$this->cookies) return '';
        $cookies = '';
        foreach ($this->cookies as $name => $value) {
            $cookies .= "        $name=\"$value\";\r\n";
        }
        return "Cookie: " . ltrim($cookies);
    }
    
    
    // POST ------------------------------------------------------------------------------------------------------------
    
    
    private function setRawPostContent($content) {
        return $this->setOption('http', 'content', $content);
    }
    
    
    public function addPostData($data) {
        $this->postData = $data;
    }
    
    
    /*public function addPostFile($file) {
        ///
    }*/
    
    
    private function compileContent() {
        if ($this->method === 'post' && $this->postData) {
            $this->addHeader('Content-type', 'application/x-www-form-urlencoded');
        }
        
        $data = http_build_query($this->postData);
        /// files
        $this->setRawPostContent($data);
    }
    
    
    private function compileFiles() {
        ///
    }
    
}
