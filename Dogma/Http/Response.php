<?php

namespace Dogma\Http;

use Nette\Utils\Strings;


class Response extends \Dogma\Object {
    
    /** @var int CURLE_* */
    private $error;
    
    /** @var array */
    protected $info;
    
    /** @var int */
    private $status;
    
    /** @var string */
    private $response;
    
    /** @var array */
    protected $headers = array();
    
    /** @var string */
    protected $body;
    
    

    /**
     * @param string
     * @param array
     * @param int
     */
    public function __construct($response, array $info, $error) {
        $this->error = $error;
        $this->info = $info;
        
        if ($error) return;
        
        $this->status = $info['http_code'];
        
        if (!$response) return;
        
        $this->response = $response;
    }

    
    /**
     * @return bool
     */
    public function isSuccess() {
        return !$this->error && !(substr($this->status, 0, 1) === '4' || substr($this->status, 0, 1) === '5');
    }
    
    
    /**
     * @return int
     */
    public function getErrorCode() {
        return $this->error;
    }

    
    /**
     * @return string
     */
    public function getError() {
        return CurlHelpers::getCurlErrorName($this->error);
    }
    
    
    /**
     * @return int
     */
    public function getStatusCode() {
        return $this->status;
    }


    /**
     * @return int
     */
    public function getStatus() {
        $code = HttpCode::instance($this->status);
        
        return $code->name;
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
     * @return array
     */
    public function getHeaders() {
        if ($this->response) $this->parseResponse();
        
        return $this->headers;
    }


    /**
     * @param string
     * @param string
     * @return self
     */
    public function convert($to = "UTF-8", $from = NULL) {
        if ($from === NULL) {
            $charset = $this->query['head > meta[http-equiv=Content-Type]']->attr('content');
            $charset = $charset ?: $this->query['head > meta[http-equiv=content-type]']->attr('content');
            $charset = $charset ?: $this->headers['Content-Type'];

            $from = static::getCharset($charset);
        }

        $from = Strings::upper($from);
        $to = Strings::upper($to);

        if ($from != $to && $from && $to) {
            if ($body = @iconv($from, $to, $this->body)) {
                $this->Body = ltrim($body);

            } else {
                throw new CurlException("Charset conversion from $from to $to failed");
            }
        }

        $this->Body = self::fixContentTypeMeta($this->body);

        return $this;
    }
    

    /**
     * @param string $header
     * @return string
     */
    public static function getCharset($header, $default = NULL) {
        $match = Strings::match($header, self::CONTENT_TYPE);
        return isset($match['charset']) ? $match['charset'] : $default;
    }


    /**
     * @param string $header
     * @return string
     */
    public static function getContentType($header, $default = NULL) {
        $match = Strings::match($header, self::CONTENT_TYPE);
        return isset($match['type']) ? $match['type'] : $default;
    }
    
    
    /**
     * @return string
     */
    public function getBody() {
        if ($this->response) $this->parseResponse();
        /// file
        
        return $this->body;
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

        // Extract the version and status from the first header
        $version_and_status = array_shift($headers);
        $matches = Strings::match($version_and_status, '~HTTP/(?P<version>\d\.\d)\s(?P<code>\d\d\d)\s(?P<status>.*)~');
        if (count($matches) > 0) {
            $found['Http-Version'] = $matches['version'];
            $found['Status-Code'] = $matches['code'];
            $found['Status'] = $matches['code'] . ' ' . $matches['status'];
        }

        // Convert headers into an associative array
        foreach ($headers as $header) {
            $matches = Strings::match($header, '~(?P<header>.*?)\:\s(?P<value>.*)~');
            $found[$matches['header']] = $matches['value'];
        }

        return $found;
    }
    
}
