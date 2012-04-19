<?php

namespace Dogma\Http;

use Nette\Utils\Strings;


/**
 * HTTP request. Holds a CURL resource.
 */
class Request extends \Dogma\Object {

    const GET = 'get';
    const HEAD = 'head';
    const POST = 'post';
    const PUT = 'put';
    const DELETE = 'delete';
    const TRACE = 'trace';
    const OPTIONS = 'options';
    const CONNECT = 'connect';


    /** @var resource */
    protected $curl;

    /** @var string */
    protected $url;

    /** @var array */
    private $headers = array();
    
    
    public function __construct($url = NULL) {
        $this->curl = curl_init();
        $this->setOption(CURLOPT_HEADER, TRUE);
        if ($url) $this->setUrl($url);
    }


    // basic operations ------------------------------------------------------------------------------------------------

    
    /**
     * @param string
     * @return self
     */
    public function setUrl($url) {
        $this->setOption(CURLOPT_URL, $url);
        $this->url = $url;

        return $this;
    }

    
    /**
     * @param string
     * @return self
     */
    public function appendUrl($url) {
        $this->setUrl($this->url . $url);

        return $this;
    }

    
    /**
     * @param string
     * @return self
     */
    public function setMethod($method) {
        $method = strtolower($method);

        switch ($method) {
            case self::GET:
                $this->setOption(CURLOPT_HTTPGET, TRUE);
                break;
            case self::HEAD:
                $this->setOption(CURLOPT_NOBODY, TRUE);
                break;
            case self::POST:
                $this->setOption(CURLOPT_POST, TRUE);
                break;
            case self::PUT:
                $this->setOption(CURLOPT_UPLOAD, TRUE);
                break;
            case self::DELETE:
            case self::TRACE:
            case self::OPTIONS:
            case self::CONNECT:
                $this->setOption(CURLOPT_CUSTOMREQUEST, $method);
                break;
            default:
                throw new RequestException("Unknown method '$method'!");
        }

        return $this;
    }

    
    /**
     * @param array
     * @return self
     */
    public function setPostData(array $data) {
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        //$this->setMethod(self::POST); // should be automatic?
        
        return $this;
    }


    /**
     * @param string|int option name or CURLOPT_ constant
     * @param mixed
     * @return self
     */
    public function setOption($name, $value) {
        if (is_string($name)) {
            $num = CurlHelpers::getCurlOptionNumber($name);
            if (is_null($num))
                throw new RequestException("Unknown CURL option '$name'!");
            
        } elseif (!is_int($name)) {
            throw new RequestException("Option name must be a string or a CURLOPT_* constant!");
            
        } else {
            $num = $name;
        }

        if (!curl_setopt($this->curl, $num, $value))
            throw new RequestException("Invalid CURL option.");

        return $this;
    }


    // connection options ----------------------------------------------------------------------------------------------

    
    /**
     * @param float|int
     * @param float|int
     * @return self
     */
    public function setTimeout($timeout, $connectTimeout = NULL) {
        if ($timeout < 1) {
            $this->setOption(CURLOPT_TIMEOUT_MS, (int)($timeout / 1000));
        } else {
            $this->setOption(CURLOPT_TIMEOUT, (int) $timeout);
        }

        if (is_null($connectTimeout)) return $this;

        if ($timeout < 1) {
            $this->setOption(CURLOPT_CONNECTTIMEOUT_MS, (int)($timeout / 1000));
        } else {
            $this->setOption(CURLOPT_CONNECTTIMEOUT, (int) $timeout);
        }

        return $this;
    }


    /**
     * @param bool
     * @param int
     * @return self
     */
    public function setFollowRedirects($follow = TRUE, $max = NULL) {
        $this->setOption(CURLOPT_FOLLOWLOCATION, $follow);
        if (!is_null($max)) $this->setOption(CURLOPT_MAXREDIRS, (int) $max);

        return $this;
    }


    /**
     * @param string
     * @param string
     * @return self
     */
    public function addHeader($name, $value) {
        $this->headers[$name] = $value;
        
        return $this;
    }


    /**
     * @param array
     * @return self
     */
    public function setHeaders(array $headers) {
        $this->headers = $headers;
        
        return $this;
    }


    protected function setRequestHeaders() {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            if (is_int($key)) {
                $headers[] = $value;
                continue;
            }
            
            //fix HTTP_ACCEPT_CHARSET to Accept-Charset
            $key = Strings::replace($key, array('/^HTTP_/i' => '', '/_/' => '-'));
            $key = Strings::replace($key, '/(?P<word>[a-z]+)/i', function ($match) {
                return ucfirst(strtolower(current($match)));
            });

            if ($key == 'Et') $key = 'ET';

            $headers[] = $key . ': ' . $value;
        }

        if ($this->headers)
            $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }
    

    /**
     * @param string
     * @return self
     */
    public function setReferrer($url) {
        $this->setOption(CURLOPT_REFERER, $url); 

        return $this;
    }


    /**
     * @param string
     * @return self
     */
    public function setUserAgent($string) {
        $this->setOption(CURLOPT_USERAGENT, $string);

        return $this;
    }


    // authentication & secure connection ------------------------------------------------------------------------------


    /**
     * @param string
     * @param string
     * @param int $method CURLAUTH_ constant
     * @return self
     */
    public function setCredentials($userName, $password, $method = CURLAUTH_ANYSAFE) {
        $this->setOption(CURLOPT_USERPWD, $userName . ':' . $password);
        $this->setOption(CURLOPT_HTTPAUTH, $method);
        
        return $this;
    }


    /**
     * @param string
     * @param string
     * @param string (PEM|DER|ENG)
     * @return self
     */
    public function setSslKey($keyFile, $password, $keyType = 'PEM') {
        $this->setOption(CURLOPT_SSLKEY, $keyFile);
        $this->setOption(CURLOPT_SSLKEYPASSWD, $password);
        $this->setOption(CURLOPT_SSLKEYTYPE, $keyType);
        
        return $this;
    }


    /**
     * @param bool
     * @param bool
     * @return self
     */
    public function setVerifySslCertificates($verifyPeer = TRUE, $verifyHost = TRUE) {
        $this->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);

        return $this;
    }


    // output handling -------------------------------------------------------------------------------------------------

    
    /**
     * Execute request.
     * @param string
     * @param string
     * @return Response
     */
    public function execute($url = NULL) {
        $this->prepare($url);
        list($response, $error) = $this->sendRequest();
        return $this->createResponse($response, $error, '');
    }


    /**
     * Called by RequestManager.
     * @internal
     *
     * @param string
     * @param string
     * @param bool
     */
    public function prepare($url = NULL) {
        $this->setOption(CURLOPT_RETURNTRANSFER, TRUE);
        
        $this->setRequestHeaders();
        if ($url) $this->setOption(CURLOPT_URL, $this->url . $url);
    }
    
    
    /**
     * @return array
     */
    protected function sendRequest() {
        $response = curl_exec($this->curl);
        $error = curl_errno($this->curl);

        return array($response, $error);
    }

    
    /**
     * Called by RequestManager.
     * @internal
     * 
     * @return resource
     */
    public function getHandler() {
        return $this->curl;
    }
    
    
    /**
     * Called by RequestManager.
     * @internal
     * 
     * @param string|bool
     * @param int
     * @param string
     * @return Response
     */
    public function createResponse($response, $error, $name) {
        $info = curl_getinfo($this->curl);
        if ($info === FALSE)
            throw new RequestException("Info cannot be obtained from CURL.");
        
        return new Response($response, $info, $error);
    }


    // -----------------------------------------------------------------------------------------------------------------

    
    /**
     * Copy resource.
     */
    final public function __clone() {
        if ($this->curl) {
            $this->curl = curl_copy_handle($this->curl);
        }
    }
    
}
