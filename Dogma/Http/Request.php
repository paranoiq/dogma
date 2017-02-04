<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Http\Curl\CurlHelper;

/**
 * HTTP request. Holds a CURL resource.
 */
class Request
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonSerializableMixin;

    /** @var resource(curl) */
    private $curl;

    /** @var callable|null */
    private $init;

    /** @var string */
    private $url;

    /** @var string */
    private $method = HttpMethod::GET;

    /** @var string[] */
    private $headers = [];

    /** @var string[] */
    private $cookies = [];

    /** @var mixed[] */
    private $variables = [];

    /** @var string */
    private $content;

    /** @var \Dogma\Http\HeaderParser|null */
    protected $headerParser;

    /** @var mixed */
    protected $context;

    /** @var string[] */
    protected $responseHeaders = [];

    public function __construct(string $url = null, string $method = null)
    {
        error_clear_last();
        $curl = curl_init();
        if ($curl === false) {
            throw new \Dogma\Http\RequestException(sprintf('Cannot initialize curl. Error: %s', error_get_last()['message']));
        }
        $this->curl = $curl;

        if ($url !== null) {
            $this->setUrl($url);
        }
        if ($method !== null) {
            $this->setMethod($method);
        }

        $this->setHeaderFunction();
    }

    /**
     * Called by Channel.
     * @internal
     */
    public function init()
    {
        if ($this->init !== null) {
            if (!($this->init)($this)) {
                throw new \Dogma\Http\RequestException('Request initialisation failed!');
            }
            $this->init = null;
        }
    }

    /**
     * @param callable
     */
    public function setInit(callable $init)
    {
        $this->init = $init;
    }

    public function setHeaderParser(HeaderParser $headerParser)
    {
        $this->headerParser = $headerParser;
    }

    /**
     * @return \Dogma\Http\HeaderParser|null
     */
    public function getHeaderParser()
    {
        return $this->headerParser;
    }

    /**
     * @param mixed Request context
     */
    public function setContext($data)
    {
        $this->context = $data;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    // basic operations ------------------------------------------------------------------------------------------------

    /**
     * @param string
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string
     */
    public function appendUrl(string $url)
    {
        $this->setUrl($this->url . $url);
    }

    /**
     * @param mixed
     */
    public function setData($data)
    {
        if ($data !== null) {
            $this->dispatch($data);
        }
    }

    /**
     * @param string|array
     */
    protected function dispatch($data)
    {
        if (is_string($data)) {
            $this->setContent($data);

        } elseif (is_array($data)) {
            $this->setVariables($data);

        } else {
            throw new \Dogma\Http\RequestException('Job data may be only a string or array!');
        }
    }

    public function setContent(string $data)
    {
        if ($this->method === HttpMethod::POST || $this->method === HttpMethod::PUT) {
            $this->content = $data;
        } else {
            $this->appendUrl($data);
        }
    }

    /**
     * Set URL or POST variables. Can be called repeatedly.
     * @param mixed[]
     */
    public function setVariables(array $variables)
    {
        $this->variables = array_merge($this->variables, $variables);
    }

    public function setMethod(string $method)
    {
        $this->method = strtolower($method);

        switch ($this->method) {
            case HttpMethod::GET:
                $this->setOption(CURLOPT_HTTPGET, true);
                break;
            case HttpMethod::HEAD:
                $this->setOption(CURLOPT_NOBODY, true);
                break;
            case HttpMethod::POST:
                $this->setOption(CURLOPT_POST, true);
                break;
            case HttpMethod::PUT:
                $this->setOption(CURLOPT_PUT, true);
                break;
            case HttpMethod::PATCH:
            case HttpMethod::DELETE:
            case HttpMethod::TRACE:
            case HttpMethod::OPTIONS:
            case HttpMethod::CONNECT:
                $this->setOption(CURLOPT_CUSTOMREQUEST, $this->method);
                break;
            default:
                throw new \Dogma\Http\RequestException(sprintf('Unknown method \'%s\'!', $this->method));
        }
    }

    /**
     * @param string|int option name or CURLOPT_ constant
     * @param mixed
     */
    public function setOption($name, $value)
    {
        if (is_string($name)) {
            $num = CurlHelper::getCurlOptionNumber($name);
            if (is_null($num)) {
                throw new \Dogma\Http\RequestException(sprintf('Unknown CURL option \'%s\'!', $name));
            }

        } elseif (!is_int($name)) {
            throw new \Dogma\Http\RequestException('Option name must be a string or a CURLOPT_* constant!');

        } else {
            $num = $name;
        }

        if (!curl_setopt($this->curl, $num, $value)) {
            throw new \Dogma\Http\RequestException('Invalid CURL option.');
        }
    }

    // connection options ----------------------------------------------------------------------------------------------

    /**
     * @param float|int
     * @param float|int
     */
    public function setTimeout($timeout, $connectTimeout = null)
    {
        if ($timeout < 1) {
            $this->setOption(CURLOPT_TIMEOUT_MS, (int) ($timeout / 1000));
        } else {
            $this->setOption(CURLOPT_TIMEOUT, (int) $timeout);
        }

        if (is_null($connectTimeout)) {
            return;
        }

        if ($connectTimeout < 1) {
            $this->setOption(CURLOPT_CONNECTTIMEOUT_MS, (int) ($connectTimeout / 1000));
        } else {
            $this->setOption(CURLOPT_CONNECTTIMEOUT, (int) $connectTimeout);
        }
    }

    public function setFollowRedirects(bool $follow = true, int $max = null)
    {
        $this->setOption(CURLOPT_FOLLOWLOCATION, $follow);
        if (!is_null($max)) {
            $this->setOption(CURLOPT_MAXREDIRS, (int) $max);
        }
    }

    public function addHeader(string $name, string $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * @param string[]
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    public function setReferrer(string $url)
    {
        $this->setOption(CURLOPT_REFERER, $url);
    }

    public function setUserAgent(string $string)
    {
        $this->setOption(CURLOPT_USERAGENT, $string);
    }

    // cookies, authentication & secure connection ---------------------------------------------------------------------

    public function setCookieFile(string $inputFile, string $outputFile = null)
    {
        if ($inputFile) {
            $this->setOption(CURLOPT_COOKIEFILE, $inputFile);
        }
        if ($outputFile) {
            $this->setOption(CURLOPT_COOKIEJAR, $outputFile);
        }
    }

    /**
     * @param string[]
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }

    public function addCookie(string $name, string $value)
    {
        $this->cookies[$name] = $value;
    }

    public function setCredentials(string $userName, string $password, int $method = CURLAUTH_ANYSAFE)
    {
        $this->setOption(CURLOPT_USERPWD, $userName . ':' . $password);
        $this->setOption(CURLOPT_HTTPAUTH, $method);
    }

    public function setSslKey(string $keyFile, string $password, string $keyType = 'PEM')
    {
        $this->setOption(CURLOPT_SSLKEY, $keyFile);
        $this->setOption(CURLOPT_SSLKEYPASSWD, $password);
        $this->setOption(CURLOPT_SSLKEYTYPE, $keyType);
    }

    public function setVerifySslCertificates(bool $verifyPeer = true, bool $verifyHost = true)
    {
        $this->setOption(CURLOPT_SSL_VERIFYPEER, $verifyPeer);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, $verifyHost);
    }

    public function setProxy(string $proxy = null, $port = 3128, $userName = null, $password = null)
    {
        if ($proxy === null) {
            $this->setOption(CURLOPT_PROXY, null);
            $this->setOption(CURLOPT_PROXYPORT, null);
            $this->setOption(CURLOPT_PROXYUSERPWD, null);
        } else {
            $this->setOption(CURLOPT_PROXY, $proxy);
            $this->setOption(CURLOPT_PROXYPORT, $port);
            $this->setOption(CURLOPT_PROXYUSERPWD, ($userName && $password) ? $userName . ':' . $password : null);
        }
    }

    // output handling -------------------------------------------------------------------------------------------------

    public function execute(): Response
    {
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
    public function prepare()
    {
        $params = $this->analyzeUrl();
        if ($params || $this->variables || $this->content) {
            $this->prepareData($params);
        }

        $this->setOption(CURLOPT_URL, $this->url);
        $this->setOption(CURLOPT_RETURNTRANSFER, true);

        if ($this->headers) {
            $this->prepareHeaders();
        }
        if ($this->cookies) {
            $this->prepareCookies();
        }
    }

    /**
     * Called by RequestManager.
     * @internal
     *
     * @return resource
     */
    public function getHandler()
    {
        return $this->curl;
    }

    /**
     * Called by RequestManager.
     * @internal
     *
     * @param string|bool $response
     * @param int $error
     * @return \Dogma\Http\Response
     */
    public function createResponse($response, int $error)
    {
        $info = $this->getInfo();
        $status = $this->getResponseStatus($error, $info);

        return new Response($status, $response, $this->responseHeaders, $info, $this->context, $this->headerParser);
    }

    // internals -------------------------------------------------------------------------------------------------------

    protected function getInfo(): array
    {
        $info = curl_getinfo($this->curl);
        if ($info === false) {
            throw new \Dogma\Http\RequestException('Info cannot be obtained from CURL.');
        }

        return $info;
    }

    protected function getResponseStatus(int $error, array $info): ResponseStatus
    {
        if ($error !== 0) {
            $status = ResponseStatus::get($error);
        } else {
            try {
                $status = ResponseStatus::get($info['http_code']);
            } catch (\Dogma\InvalidValueException $e) {
                $status = ResponseStatus::get(ResponseStatus::UNKNOWN_RESPONSE_CODE);
            }
        }
        if ($status->isFatalError()) {
            throw new \Dogma\Http\RequestException(sprintf('Fatal error occurred during request execution: %s', $status->getConstantName()), $status->getValue());
        }

        return $status;
    }

    private function prepareHeaders()
    {
        $headers = [];
        foreach ($this->headers as $key => $value) {
            if (is_int($key)) {
                $headers[] = $value;
            } else {
                $headers[] = $key . ': ' . $value;
            }
        }

        $this->setOption(CURLOPT_HTTPHEADER, $headers);
    }

    private function prepareCookies()
    {
        $cookie = '';
        foreach ($this->cookies as $name => $value) {
            $cookie .= sprintf('; %s=%s', $name, $value);
        }

        $this->setOption(CURLOPT_COOKIE, substr($cookie, 2));
    }

    /**
     * @param mixed[] $variables
     */
    private function prepareData(array $variables)
    {
        if ($variables) {
            $this->fillUrlVariables($variables);
        }

        if ($this->content !== null && $this->variables !== []) {
            throw new \Dogma\Http\RequestException('Both data content and variables are set. Only one at a time can be sent!');
        }

        if ($this->content) {
            $this->prepareUpload();
        }

        if ($this->variables === []) {
            return;
        }

        if ($this->method === HttpMethod::POST) {
            $this->preparePost();
        } else {
            $names = array_keys($this->variables);
            throw new \Dogma\Http\RequestException(
                'Redundant request variable' . (count($names) > 1 ? 's' : '') . ' \'' . implode("', '", $names) . '\' in request data.'
            );
        }
    }

    private function prepareUpload()
    {
        $this->setOption(CURLOPT_BINARYTRANSFER, true);

        if (substr($this->content, 0, 1) === '@') {
            $fileName = substr($this->content, 1);
            $file = fopen($fileName, 'r');
            if (!$file) {
                throw new \Dogma\Http\RequestException(sprintf('Could not open file %s.', $fileName));
            }

            $this->setOption(CURLOPT_INFILE, $file);
            $this->setOption(CURLOPT_INFILESIZE, strlen($this->content));

        } else {
            $this->setOption(CURLOPT_POSTFIELDS, $this->content);
        }
    }

    private function preparePost()
    {
        foreach ($this->variables as $name => $value) {
            if ($value === null) {
                throw new \Dogma\Http\RequestException(sprintf('POST parameter \'%s\' must be filled.', $name));
            }
        }
        $this->setOption(CURLOPT_POSTFIELDS, $this->variables);
        $this->variables = [];
    }

    /**
     * @return mixed[]
     */
    private function analyzeUrl(): array
    {
        $params = [];

        if (preg_match_all('/\\W([0-9A-Za-z_]+)=%[^0-9A-Fa-f]/', $this->url, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $params[$m[1]] = true;
            }
        }
        if (preg_match_all('/{%([0-9A-Za-z_]+)}/', $this->url, $mm, PREG_SET_ORDER)) {
            foreach ($mm as $m) {
                $params[$m[1]] = false;
            }
        }

        return $params;
    }

    /**
     * @param mixed[]
     */
    private function fillUrlVariables(array $vars)
    {
        foreach ($vars as $name => $short) {
            if (!isset($this->variables[$name])) {
                throw new \Dogma\Http\RequestException(sprintf('URL variable \'%s\' is missing in request data.', $name));
            }

            if ($short) {
                $this->url = preg_replace(sprintf('/(?<=\\W%s=)%(?=[^0-9A-Fa-f])/', $name), urlencode($this->variables[$name]), $this->url);
            } else {
                $this->url = preg_replace(sprintf('/\\{%%%s\\}/', $name), urlencode($this->variables[$name]), $this->url);
            }

            unset($this->variables[$name]);
        }
    }

    final public function __clone()
    {
        if ($this->curl) {
            $this->curl = curl_copy_handle($this->curl);
        }
        // need to set the closure to $this again
        $this->setHeaderFunction();
    }

    private function setHeaderFunction()
    {
        $this->setOption(CURLOPT_HEADERFUNCTION, function ($curl, string $header) {
            $length = strlen($header);
            $header = trim($header);
            if ($header !== '') {
                $this->responseHeaders[] = $header;
            }

            return $length;
        });
    }

}
