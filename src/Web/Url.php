<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Web;

use Dogma\Check;
use Dogma\Equalable;
use Dogma\InvalidArgumentException;
use Dogma\StrictBehaviorMixin;
use function array_slice;
use function explode;
use function http_build_query;
use function implode;
use function ini_get;
use function ip2long;
use function is_array;
use function parse_str;
use function parse_url;
use function preg_quote;
use function preg_replace;
use function rawurldecode;
use function str_replace;
use const PHP_QUERY_RFC3986;

/**
 * Immutable representation of a URL.
 *
 * <pre>
 * scheme  user password    host     port     path        query   fragment
 *   |      |      |         |        |        |            |        |
 * /--\   /--\ /-------\ /---------\ /--\/-----------\ /--------\ /------\
 * http://john:1337h4x0r@example.com:8042/en/index.php?name=param#fragment  <-- absoluteUrl
 * \______\_____________________________/
 *     |               |
 *  hostUrl        authority
 * </pre>
 */
class Url implements Equalable
{
    use StrictBehaviorMixin;

    private string $scheme;

    private string $user;

    private string $password;

    private string $host;

    private int|null $port;

    private string $path;

    /** @var array<string> */
    private array $query = [];

    private string $fragment;

    private string $authority;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $url)
    {
        $p = @parse_url($url);
        if ($p === false) {
            throw new InvalidArgumentException("Malformed or unsupported URI '{$url}'.");
        }

        $this->scheme = $p['scheme'] ?? '';
        $this->port = $p['port'] ?? null;
        $this->host = rawurldecode($p['host'] ?? '');
        $this->user = rawurldecode($p['user'] ?? '');
        $this->password = rawurldecode($p['pass'] ?? '');
        $this->path = $p['path'];
        if ($this->host && $this->path[0] !== '/') {
            $this->path = '/' . $this->path;
        }
        $this->query = is_array($p['query']) ? $p['query'] : self::parseQuery($p['query'] ?? '');
        $this->fragment = rawurldecode($p['fragment'] ?? '');
    }

    public function getValue(): string
    {
        return $this->getAbsoluteUrl();
    }

    /**
     * URI including query string and fragment
     */
    public function getAbsoluteUrl(): string
    {
        return $this->getHostUrl() . $this->path
            . (($tmp = $this->getQuery()) ? '?' . $tmp : '')
            . ($this->fragment === '' ? '' : '#' . $this->fragment);
    }

    /**
     * Scheme and authority part of URI
     */
    public function getHostUrl(): string
    {
        return ($this->scheme ? $this->scheme . ':' : '')
            . ($this->authority !== '' ? '//' . $this->authority : '');
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getHostEnum(): Domain
    {
        return new Domain($this->host);
    }

    public function getDomain(int $level = 2): string
    {
        $parts = ip2long($this->host)
            ? [$this->host]
            : explode('.', $this->host);

        $parts = $level >= 0
            ? array_slice($parts, -$level)
            : array_slice($parts, 0, $level);

        return implode('.', $parts);
    }

    public function getDomainEnum(int $level = 2): Domain
    {
        return new Domain($this->getDomain($level));
    }

    public function getTld(): string
    {
        return $this->getDomainEnum()->getTld()->getValue();
    }

    public function getTldEnum(): Tld
    {
        return $this->getDomainEnum()->getTld();
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return http_build_query($this->query, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @return array<string>
     */
    public function getQueryParameters(): array
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param self $other
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->getAbsoluteUrl() === $other->getAbsoluteUrl();
    }

    /**
     * @return array<string>
     */
    public static function parseQuery(string $s): array
    {
        $s = str_replace(['%5B', '%5b'], '[', $s);
        $sep = preg_quote(ini_get('arg_separator.input'), '~');
        $s = preg_replace("~([$sep])([^[$sep=]+)([^$sep]*)~", '&0[$2]$3', '&' . $s);
        parse_str($s, $res);

        return $res[0] ?? [];
    }

}
