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
use Dogma\Language\Encoding;
use Dogma\Language\Locale\Locale;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use Dogma\Time\Provider\CurrentTimeProvider;
use Dogma\Web\Host;
use Dogma\Web\Url;
use function is_int;

class HttpResponse
{
    use StrictBehaviorMixin;

    /** @var mixed[] */
    protected array $info;

    private HttpOrCurlStatus $status;

    /** @var string[] */
    private array $rawHeaders;

    /** @var mixed[]|null */
    private ?array $headers = null;

    /** @var string[]|null */
    private ?array $cookies = null;

    private ?string $body;

    /** @var mixed */
    private $context;

    private ?HttpHeaderParser $headerParser;

    /**
     * @param string[] $rawHeaders
     * @param string[] $info
     */
    public function __construct(
        HttpOrCurlStatus $status,
        ?string $body,
        array $rawHeaders,
        array $info,
        mixed $context,
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

    public function getStatus(): HttpOrCurlStatus
    {
        return $this->status;
    }

    public function getBody(): ?string
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
     * @return string|string[]|int|DateTime|Host|Url|ContentType|Encoding|Locale|null
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
            /** @var string|null $cookies */
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
        /** @var ContentType|null $contentType */
        $contentType = $this->getHeader(HttpHeader::CONTENT_TYPE);

        return $contentType;
    }

    /**
     * @return string|string[]
     */
    public function getInfo(string|int $name = null)
    {
        if ($name === null) {
            return $this->info;
        }

        if (is_int($name)) {
            $id = $name;
            $name = CurlHelper::getCurlInfoName($id);
            if ($name === null) {
                throw new HttpResponseException("Unknown CURL info '$id'!");
            }
        }

        return $this->info[$name];
    }

}
