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


class Response
{
    use \Dogma\StrictBehaviorMixin;

    /** @var array */
    protected $info;

    /** @var \Dogma\Http\ResponseStatus */
    private $status;

    /** @var string */
    private $response;

    /** @var array */
    protected $headers = [];

    /** @var string */
    protected $body;

    /** @var mixed Request context */
    protected $context;

    /**
     * @param string
     * @param \Dogma\Http\ResponseStatus
     * @param mixed[]
     */
    public function __construct(string $response, ResponseStatus $status, array $info)
    {
        $this->status = $status;
        $this->info = $info;

        if ($response) {
            $this->response = $response;
        }
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

    public function isSuccess(): bool
    {
        return $this->status->isOk();
    }

    public function getStatus(): ResponseStatus
    {
        return $this->status;
    }

    public function getBody(): string
    {
        if ($this->response) {
            $this->parseResponse();
        }

        return $this->body;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        if ($this->response) {
            $this->parseResponse();
        }

        return $this->headers;
    }

    /**
     * Get all cookies received with this response.
     * @return string[]
     */
    public function getCookies(): array
    {
        if ($this->response) {
            $this->parseResponse();
        }

        $cookies = [];

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
    public function getInfo($name = null)
    {
        if (is_null($name)) {
            return $this->info;
        }

        if (is_int($name)) {
            $tname = CurlHelpers::getCurlInfoName($name);
        } else {
            $tname = $name;
        }
        if (is_null($tname)) {
            throw new ResponseException(sprintf('Unknown CURL info \'%s\'!', $name));
        }

        return $this->info[$tname];
    }

    public function __toString(): string
    {
        return (string) $this->getBody();
    }

    // internals -------------------------------------------------------------------------------------------------------

    /**
     * Remove headers from response.
     */
    private function parseResponse()
    {
        $headers = Strings::split(substr($this->response, 0, $this->info['header_size']), "~[\n\r]+~", PREG_SPLIT_NO_EMPTY);
        $this->headers = static::parseHeaders($headers);
        $this->body = substr($this->response, $this->info['header_size']);
        $this->response = null;
    }

    /**
     * Parses headers from given list
     * @param string[]
     * @return string[]
     */
    public static function parseHeaders(array $headers): array
    {
        $found = [];

        // extract version and status
        $versionAndStatus = array_shift($headers);
        $m = Strings::match($versionAndStatus, '~HTTP/(?P<version>\d\.\d)\s(?P<code>\d\d\d)\s(?P<status>.*)~');
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
                    $found[$m['header']] = [$found[$m['header']]];
                    $found[$m['header']][] = $m['value'];
                }
            } else {
                $found[$m['header']] = $m['value'];
            }
        }

        return $found;
    }

}
