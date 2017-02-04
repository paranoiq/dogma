<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Check;
use Dogma\Io\ContentType\ContentType;
use Dogma\Language\Encoding;
use Dogma\Language\Locale\Locale;
use Dogma\Str;
use Dogma\Time\DateTime;
use Dogma\Time\TimeProvider;
use Dogma\Type;
use Dogma\Web\Host;
use Dogma\Web\Url;

class HeaderParser
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string[] */
    private static $types = [
        HttpHeader::AGE => Type::INT,
        HttpHeader::CONTENT_LANGUAGE => Locale::class,
        HttpHeader::CONTENT_LENGTH => Type::INT,
        HttpHeader::CONTENT_TYPE => ContentType::class,
        HttpHeader::DATE => DateTime::class,
        HttpHeader::EXPIRES => DateTime::class,
        HttpHeader::HOST => Host::class,
        HttpHeader::IF_MODIFIED_SINCE => DateTime::class,
        HttpHeader::IF_UNMODIFIED_SINCE => DateTime::class,
        HttpHeader::LAST_MODIFIED => DateTime::class,
        HttpHeader::LOCATION => Url::class,
        HttpHeader::MAX_FORWARDS => Type::INT,
        HttpHeader::ORIGIN => Url::class,
        HttpHeader::REFERER => Url::class,
        HttpHeader::X_FORWARDED_HOST => Host::class,
        HttpHeader::X_WAP_PROFILE => Url::class,
    ];

    /** @var \Dogma\Time\TimeProvider */
    private $timeProvider;

    public function __construct(TimeProvider $timeProvider)
    {
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param string[]
     * @return mixed[]
     */
    public function parseHeaders(array $rawHeaders): array
    {
        $headers = [];

        $versionAndStatus = array_shift($rawHeaders);
        $parts = Str::match($versionAndStatus, '~HTTP/(\d\.\d)\s(\d\d\d)\s(.*)~');
        if ($parts !== null) {
            $headers[HttpHeader::HTTP_VERSION] = $parts[1];
            $headers[HttpHeader::STATUS] = $parts[2] . ' ' . $parts[3];
        } else {
            array_unshift($rawHeaders, $versionAndStatus);
        }

        foreach ($rawHeaders as $header) {
            list($name, $value) = Str::splitByFirst($header, ':');
            $name = HttpHeader::normalizeName(trim($name));

            if ($name === HttpHeader::CONTENT_TYPE && Str::contains($value, ';')) {
                list($value, $charset) = Str::splitByFirst($value, ';');
                $charset = Str::fromFirst($charset, '=');
                $this->insertHeader($headers, HttpHeader::CONTENT_CHARSET, $this->formatValue(trim($charset), Encoding::class));
            }

            $type = self::$types[$name] ?? null;
            if ($type !== null) {
                $this->insertHeader($headers, $name, $this->formatValue(trim($value), $type));
            } else {
                $this->insertHeader($headers, $name, trim($value));
            }
        }

        return $headers;
    }

    /**
     * @param string|string[] $rawCookies
     * @return string[]
     */
    public function parseCookies($rawCookies): array
    {
        if (!is_array($rawCookies)) {
            $rawCookies = [$rawCookies];
        }

        $cookies = [];
        foreach ($rawCookies as $cookie) {
            $parts = explode(';', $cookie);
            list($name, $value) = explode('=', trim($parts[0]));
            $cookies[$name] = $value;
        }

        return $cookies;
    }

    private function insertHeader(array &$headers, $name, $value)
    {
        if (isset($headers[$name])) {
            if (is_array($headers[$name])) {
                $headers[$name][] = $value;
            } else {
                $headers[$name] = [$headers[$name], $value];
            }
        } else {
            $headers[$name] = $value;
        }
    }

    /**
     * @param string $value
     * @param string $type
     * @return string|int|\Dogma\Time\DateTime|\Dogma\Web\Host|\Dogma\Web\Url|\Dogma\Io\ContentType\ContentType|\Dogma\Language\Encoding|\Dogma\Language\Locale\Locale
     */
    private function formatValue(string $value, string $type)
    {
        switch ($type) {
            case Type::INT:
                Check::int($value);
                return $value;
            case DateTime::class:
                return DateTime::createFromFormat(DateTime::FORMAT_EMAIL_HTTP, $value)
                    ->setTimezone($this->timeProvider->getTimeZone());
            case Host::class:
                list($host, $port) = Str::splitByFirst($value, ':');
                return new Host($host, $port);
            case Url::class:
                return new Url($value);
            case ContentType::class:
                return ContentType::get($value);
            case Encoding::class:
                return Encoding::get($value);
            case Locale::class:
                return Locale::get($value);
            default:
                return $value;
        }
    }

}
