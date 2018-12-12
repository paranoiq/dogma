<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Http\Curl\CurlHelper;
use Dogma\Io\ContentType\ContentType;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Provider\CurrentTimeProvider;
use function is_int;
use function sprintf;

class HttpResponse
{
    use StrictBehaviorMixin;

    /** @var mixed[] */
    protected $info;

    /** @var \Dogma\Http\HttpResponseStatus */
    private $status;

    /** @var string[] */
    private $rawHeaders;

    /** @var mixed[] */
    private $headers;

    /** @var string[] */
    private $cookies;

    /** @var string */
    private $body;

    /** @var mixed */
    private $context;

    /** @var \Dogma\Http\HttpHeaderParser */
    private $headerParser;

    /**
     * @param \Dogma\Http\HttpResponseStatus $status
     * @param string|null $body
     * @param string[] $rawHeaders
     * @param string[] $info
     * @param mixed $context
     * @param \Dogma\Http\HttpHeaderParser|null $headerParser
     */
    public function __construct(
        HttpResponseStatus $status,
        ?string $body,
        array $rawHeaders,
        array $info,
        $context,
        ?HttpHeaderParser $headerParser = null
    ) {
        $this->status = $status;
        $this->body = $body;
        $this->rawHeaders = $rawHeaders;
        $this->info = $info;
        $this->context = $context;
        $this->headerParser = $headerParser;
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

    public function getStatus(): HttpResponseStatus
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        if ($this->headers === null) {
            $this->headers = $this->getHeaderParser()->parseHeaders($this->rawHeaders);
        }
        return $this->headers;
    }

    /**
     * @param string $name
     * @return string|string[]|int|\Dogma\Time\DateTime|\Dogma\Web\Host|\Dogma\Web\Url|\Dogma\Io\ContentType\ContentType|\Dogma\Language\Encoding|\Dogma\Language\Locale\Locale
     */
    public function getHeader(string $name)
    {
        if ($this->headers === null) {
            $this->getHeaders();
        }
        return $this->headers[$name] ?? null;
    }

    private function getHeaderParser(): HttpHeaderParser
    {
        if ($this->headerParser === null) {
            $this->headerParser = new HttpHeaderParser(new CurrentTimeProvider());
        }
        return $this->headerParser;
    }

    /**
     * @return string[]
     */
    public function getCookies(): array
    {
        if ($this->cookies === null) {
            $cookies = $this->getHeader(HttpHeader::COOKIE);
            if ($cookies === null) {
                return [];
            }
            $this->cookies = $this->getHeaderParser()->parseCookies($cookies);
        }

        return $this->cookies;
    }

    public function getContentType(): ?ContentType
    {
        return $this->getHeader(HttpHeader::CONTENT_TYPE);
    }

    /**
     * @param string|int $name
     * @return string|string[]
     */
    public function getInfo($name = null)
    {
        if ($name === null) {
            return $this->info;
        }

        if (is_int($name)) {
            $id = $name;
            $name = CurlHelper::getCurlInfoName($id);
            if ($name === null) {
                throw new HttpResponseException(sprintf('Unknown CURL info \'%s\'!', $id));
            }
        }

        return $this->info[$name];
    }

}
