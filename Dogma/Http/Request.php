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
use Nette\Callback;


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
    
    /** @var string */
    protected $method = self::GET;
    
    /** @var array */
    private $headers = array();
    
    /** @var array */
    private $cookies = array();
    
    /** @var array */
    private $variables = array();
    
    /** @var string POST|PUT content */
    private $content;
    
    /** @var mixed Request context */
    private $context;
    
    /** @var \Nette\Callback */
    private $init;
    
    
    public function __construct($url = NULL) {
        $this->curl = curl_init();
        $this->setOption(CURLOPT_HEADER, TRUE);
        if ($url) $this->setUrl($url);
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
     * @param Callback(@param Request, @return bool)
     * @return self
     */
    public function setInit(Callback $init) {
        $this->init = $init;
        
        return $this;
    }
    
    
    /**
     * Called by RequestManager 
     * @internal
     */
    public function init() {
        if ($this->init) {
            if (!$this->init->invoke($this)) 
                throw new RequestException("Request initialisation failed!");
                
            $this->init = NULL;
        }
    }
    
    
    // basic operations ------------------------------------------------------------------------------------------------

    
    /**
     * @param string
     * @return self
     */
    public function setUrl($url) {
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
     * @param mixed
     * @return mixed
     */
    public function setData($data) {
        if ($data !== NULL) $this->dispatch($data);
    }
    
    
    /**
     * @param string|array
     */
    protected function dispatch($data) {
        if (is_string($data)) {
            $this->setContent($data);
            
        } elseif (is_array($data)) {
            $this->setVariables($data);
            
        } else {
            throw new RequestException("Job data may be only a string or array!");
        }
    }
    
    
    /**
     * @param string
     * @return self
     */
    public function setContent($data) {
        if ($this->method === self::POST || $this->method === self::PUT) {
            $this->content = $data;
        } else {
            //$this->appendUrl($data); // ?
            throw new \Nette\InvalidStateException("Cannot set content of a '$this->method' request.");
        }
            
        return $this;
    }
    

    /**
     * Set URL or POST variables. Can be called repeatedly.
     * @param array
     * @return self
     */
    public function setVariables(array $variables) {
        $this->variables = array_merge($this->variables, $variables);

        return $this;
    }
    
    
    /**
     * @param string
     * @return self
     */
    public function setMethod($method) {
        $this->method = strtolower($method);

        switch ($this->method) {
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
                $this->setOption(CURLOPT_PUT, TRUE);
                break;
            case self::DELETE:
            case self::TRACE:
            case self::OPTIONS:
            case self::CONNECT:
                $this->setOption(CURLOPT_CUSTOMREQUEST, $this->method);
                break;
            default:
                throw new RequestException("Unknown method '$this->method'!");
        }

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
            throw new RequestException("Invalid CURL option."); ///

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

        if ($connectTimeout < 1) {
            $this->setOption(CURLOPT_CONNECTTIMEOUT_MS, (int)($connectTimeout / 1000));
        } else {
            $this->setOption(CURLOPT_CONNECTTIMEOUT, (int) $connectTimeout);
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


    // cookies, authentication & secure connection ---------------------------------------------------------------------


    /**
     * @param string
     * @param string
     * @return self
     */
    public function setCookieFile($inputFile, $outputFile = NULL) {
        if ($inputFile) $this->setOption(CURLOPT_COOKIEFILE, $inputFile);
        if ($outputFile) $this->setOption(CURLOPT_COOKIEJAR, $outputFile);

        return $this;
    }
    
    
    /**
     * @param array
     * @return self
     */
    public function setCookies(array $cookies) {
        $this->cookies = $cookies;
        
        return $this;
    }
    
    
    /**
     * @param string
     * @param string
     * @return self
     */
    public function addCookie($name, $value) {
        $this->cookies[$name] = $value;

        return $this;
    }
    
    
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
     * @return Response
     */
    public function execute() {
        $this->init();
        $this->prepare();
        $response = curl_exec($this->curl);
        $error = curl_errno($this->curl);
        return $this->createResponse($response, $error);
    }


    /**
     * Called by RequestManager.
     * @internal
     */
    public function prepare() {
        $params = $this->analyzeUrl();
        if ($params || $this->variables || $this->content) $this->prepareData($params);
        
        $this->setOption(CURLOPT_URL, $this->url);
        $this->setOption(CURLOPT_RETURNTRANSFER, TRUE);
        
        if ($this->headers) $this->prepareHeaders();
        if ($this->cookies) $this->prepareCookies();
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
     * @return Response
     */
    public function createResponse($response, $error) {
        $info = curl_getinfo($this->curl);
        if ($info === FALSE)
            throw new RequestException("Info cannot be obtained from CURL.");

        if ($error) {
            $status = ResponseStatus::instance($error);
            
        } else {
            try {
                $status = ResponseStatus::instance($info['http_code']);
            } catch (\Exception $e) {
                $status = ResponseStatus::instance(ResponseStatus::UNKNOWN_RESPONSE_CODE);
            }
        }
        
        if ($status->isFatalError()) {
            throw new RequestException("Fatal error occured during request execution: $status->cname", $status->value);
        }
        
        $response = new Response($response, $status, $info);
        if ($this->context) $response->setContext($this->context);
        
        return $response;
    }


    // internals -------------------------------------------------------------------------------------------------------


    private function prepareHeaders() {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            if (is_int($key)) {
                $headers[] = $value;
                continue;
            }

            // fix HTTP_ACCEPT_CHARSET to Accept-Charset
            $key = Strings::replace($key, array('/^HTTP_/i' => '', '/_/' => '-'));
            $key = Strings::replace($key, '/(?P<word>[a-z]+)/i', function ($match) {
                return ucfirst(strtolower(current($match)));
            });

            if ($key == 'Et') $key = 'ET';

            $headers[] = $key . ': ' . $value;
        }

        $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }
    
    
    private function prepareCookies() {
        $cookie = '';
        foreach ($this->cookies as $name => $value) {
            $cookie .= "; $name=$value";
        }
        
        $this->setOption(CURLOPT_COOKIE, substr($cookie, 2)); 
    }
    
    
    /**
     * @param array
     */
    private function prepareData(array $vars) {
        if ($vars) {
            $this->fillUrlVariables($vars, $this->variables);
        }

        if ($this->content && $this->variables)
            throw new RequestException("Both data content and variables are set. Only one at a time can be sent!");

        if ($this->content)
            $this->prepareUpload();
        
        if (!$this->variables) return;
        
        if ($this->method === self::POST) {
            $this->preparePost();
            
        } else {
            $names = array_keys($this->variables);
            throw new RequestException("Redundant request variable" . (count($names) > 1 ? "s" : "") 
                . " '" . implode("', '", $names) . "' in request data.");
        }
    }
    
    
    private function prepareUpload() {
        $this->setOption(CURLOPT_BINARYTRANSFER, true);
        
        if (substr($this->content, 0, 1) === '@') {
            $fileName = substr($this->content, 1);
            $file = fopen($fileName, 'r');
            if (!$file)
                throw new RequestException("Could not open file $fileName.");

            $this->setOption(CURLOPT_INFILE, $file);
            $this->setOption(CURLOPT_INFILESIZE, strlen($this->content));
            echo "PUT upload";
            
        } else {
            $this->setOption(CURLOPT_POSTFIELDS, $this->content);
        }
    }
    
    
    private function preparePost() {
        foreach ($this->variables as $name => $value) {
            if ($value === NULL)
                throw new RequestException("POST parameter '$name' must be filled.");
        }
        $this->setOption(CURLOPT_POSTFIELDS, $this->variables);
        $this->variables = array();
    }


    /**
     * @return array
     */
    private function analyzeUrl() {
        $params = array();

        if (preg_match_all('/\\W([0-9A-Za-z_]+)=%[^0-9A-Fa-f]/', $this->url, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $params[$m[1]] = TRUE;
            }
        }
        if (preg_match_all('/{%([0-9A-Za-z_]+)}/', $this->url, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $params[$m[1]] = FALSE;
            }
        }

        return $params;
    }
    
    
    /**
     * @param array
     */
    private function fillUrlVariables(array $vars) {
        foreach ($vars as $name => $short) {
            if (!isset($this->variables[$name]))
                throw new RequestException("URL variable '$name' is missing in request data.");
            
            if ($short) {
                $this->url = preg_replace("/(?<=\\W$name=)%(?=[^0-9A-Fa-f])/", urlencode($this->variables[$name]), $this->url);
            } else {
                $this->url = preg_replace("/\\{%$name\\}/", urlencode($this->variables[$name]), $this->url);
            }
            
            unset($this->variables[$name]);
        }
    }


    /**
     * Copy resource.
     */
    final public function __clone() {
        if ($this->curl) {
            $this->curl = curl_copy_handle($this->curl);
        }
    }
    
}
