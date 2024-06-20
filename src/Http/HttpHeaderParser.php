<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use DateTimeInterface;
use DateTimeZone;
use Dogma\Check;
use Dogma\Io\ContentType\ContentType;
use Dogma\Language\Encoding;
use Dogma\Language\Locale\Locale;
use Dogma\Re;
use Dogma\StaticClassMixin;
use Dogma\Str;
use Dogma\Time\DateTime;
use Dogma\Type;
use Dogma\Web\Host;
use Dogma\Web\Url;
use function array_shift;
use function array_unshift;
use function explode;
use function is_array;
use function trim;

class HttpHeaderParser
{
    use StaticClassMixin;

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

    /**
     * @param string[] $rawHeaders
     * @return mixed[]
     */
    public static function parseHeaders(array $rawHeaders, ?DateTimeZone $useTimezone = null): array
    {
        $headers = [];

        /** @var string $versionAndStatus */
        $versionAndStatus = array_shift($rawHeaders);
        $parts = Re::match($versionAndStatus, '~HTTP/(\d\.\d)\s(\d\d\d)\s(.*)~');
        if ($parts !== null) {
            $headers[HttpHeader::HTTP_VERSION] = $parts[1];
            $headers[HttpHeader::STATUS] = $parts[2] . ' ' . $parts[3];
        } else {
            array_unshift($rawHeaders, $versionAndStatus);
        }

        foreach ($rawHeaders as $header) {
            [$name, $value] = Str::splitByFirst($header, ':');
            $name = HttpHeader::normalizeName(trim($name));

            if ($name === HttpHeader::CONTENT_TYPE && Str::contains($value, ';')) {
                [$value, $charset] = Str::splitByFirst($value, ';');
                $charset = Str::fromFirst($charset, '=');
                self::insertHeader($headers, HttpHeader::CONTENT_CHARSET, self::parseValue(trim($charset), Encoding::class));
            }

            $type = self::$types[$name] ?? null;
            if ($type !== null) {
                self::insertHeader($headers, $name, self::parseValue(trim($value), $type, $useTimezone));
            } else {
                self::insertHeader($headers, $name, trim($value));
            }
        }

        return $headers;
    }

    /**
     * @param mixed[] $headers
     * @return string[]
     */
    public static function formatHeaders(array $headers): array
    {
        $rows = [];
        foreach ($headers as $name => $value) {
            $rows[] = $name . ': ' . self::formatValue($value);
        }

        return $rows;
    }

    /**
     * @param string|string[] $rawCookies
     * @return string[]
     */
    public static function parseCookies($rawCookies): array
    {
        if (!is_array($rawCookies)) {
            $rawCookies = [$rawCookies];
        }

        $cookies = [];
        foreach ($rawCookies as $cookie) {
            $parts = explode(';', $cookie);
            [$name, $value] = explode('=', trim($parts[0]));
            $cookies[$name] = $value;
        }

        return $cookies;
    }

    /**
     * @param mixed[] $headers
     * @param mixed $value
     */
    private static function insertHeader(array &$headers, string $name, $value): void
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
     * @return string|int|DateTime|Host|Url|ContentType|Encoding|Locale
     */
    private static function parseValue(string $value, string $type, ?DateTimeZone $useTimezone = null)
    {
        switch ($type) {
            case Type::INT:
                Check::int($value);
                return $value;
            case DateTime::class:
                $dateTime = DateTime::createFromFormat(DateTime::FORMAT_EMAIL_HTTP, $value);
                if ($useTimezone) {
                    $dateTime = $dateTime->setTimezone($useTimezone);
                }
                return $dateTime;
            case Host::class:
                [$host, $port] = Str::splitByFirst($value, ':');
                return new Host($host, $port ? (int) $port : null);
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

    /**
     * @param mixed $value
     */
    private static function formatValue($value, ?DateTimeZone $useTimezone = null): string
    {
        if ($value instanceof DateTimeInterface) {
            if ($useTimezone !== null) {
                $value = DateTime::createFromDateTimeInterface($value)->setTimezone($useTimezone);
            }

            return $value->format(DateTime::FORMAT_EMAIL_HTTP);
        } elseif ($value instanceof Host) {
            return $value->format();
        } elseif ($value instanceof Url) {
            return $value->format();
        } elseif ($value instanceof ContentType) {
            return $value->getValue();
        } elseif ($value instanceof Encoding) {
            return $value->getValue();
        } elseif ($value instanceof Locale) {
            return $value->getValue();
        } else {
            return (string) $value;
        }
    }

}
