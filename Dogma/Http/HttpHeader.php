<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

class HttpHeader extends \Dogma\PartialEnum
{

    // IETF
    const ACCEPT = 'Accept';
    const ACCEPT_CHARSET = 'Accept-Charset';
    const ACCEPT_DATETIME = 'Accept-Datetime';
    const ACCEPT_ENCODING = 'Accept-Encoding';
    const ACCEPT_LANGUAGE = 'Accept-Language';
    const ACCEPT_PATCH = 'Accept-Patch';
    const ACCEPT_RANGES = 'Accept-Ranges';
    const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const AGE = 'Age';
    const ALLOW = 'Allow';
    const ALT_SVC = 'Alt-Svc';
    const AUTHORIZATION = 'Authorization';
    const CACHE_CONTROL = 'Cache-Control';
    const CONNECTION = 'Connection';
    const COOKIE = 'Cookie';
    const CONTENT_DISPOSITION = 'Content-Disposition';
    const CONTENT_ENCODING = 'Content-Encoding';
    const CONTENT_LANGUAGE = 'Content-Language';
    const CONTENT_LENGTH = 'Content-Length';
    const CONTENT_LOCATION = 'Content-Location';
    const CONTENT_MD5 = 'Content-MD5';
    const CONTENT_RANGE = 'Content-Range';
    const CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
    const CONTENT_TYPE = 'Content-Type';
    const DATE = 'Date';
    const DNT = 'DNT';
    const EXPECT = 'Expect';
    const ET = 'ET';
    const ETAG = 'ETag';
    const EXPIRES = 'Expires';
    const FORWARDED = 'Forwarded';
    const FROM = 'From';
    const HOST = 'Host';
    const IF_MATCH = 'If-Match';
    const IF_MODIFIED_SINCE = 'If-Modified-Since';
    const IF_NONE_MATCH = 'If-None-Match';
    const IF_RANGE = 'If-Range';
    const IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
    const LAST_MODIFIED = 'Last-Modified';
    const LINK = 'Link';
    const LOCATION = 'Location';
    const MAX_FORWARDS = 'Max-Forwards';
    const ORIGIN = 'Origin';
    const P3P = 'P3P';
    const PRAGMA = 'Pragma';
    const PROXY_AUTHENTICATE = 'Proxy-Authenticate';
    const PROXY_AUTHORIZATION = 'Proxy-Authorization';
    const PUBLIC_KEY_PINS = 'Public-Key-Pins';
    const RANGE = 'Range';
    const REFERER = 'Referer';
    const REFRESH = 'Refresh';
    const RETRY_AFTER = 'Retry-After';
    const SAVE_DATA = 'Save-Data';
    const SERVER = 'Server';
    const SET_COOKIE = 'Set-Cookie';
    const STATUS = 'Status';
    const STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
    const TE = 'TE';
    const TRAILER = 'Trailer';
    const TRANSFER_ENCODING = 'Transfer-Encoding';
    const TSV = 'TSV';
    const USER_AGENT = 'User-Agent';
    const UPGRADE = 'Upgrade';
    const VARY = 'Vary';
    const VIA = 'Via';
    const WARNING = 'Warning';
    const WWW_AUTHENTICATE = 'WWW-Authenticate';
    const X_FRAME_OPTIONS = 'X-Frame-Options';

    // non-standard
    const FRONT_END_HTTPS = 'Front-End-Https';
    const PROXY_CONNECTION = 'Proxy-Connection';
    const UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    const X_ATT_DEVICEID = 'X-ATT-DeviceId';
    const X_CONTENT_DURATION = 'X-Content-Duration';
    const X_CONTENT_SECURITY_POLICY = 'X-Content-Security-Policy';
    const X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
    const X_CORRELATION_ID = 'X-Correlation-ID';
    const X_CSRF_TOKEN = 'X-Csrf-Token';
    const X_DO_NOT_TRACK = 'X-Do-Not-Track';
    const X_FORWARDED_FOR = 'X-Forwarded-For';
    const X_FORWARDED_HOST = 'X-Forwarded-Host';
    const X_FORWARDED_PROTO = 'X-Forwarded-Proto';
    const X_HTTP_METHOD_OVERRIDE = 'X-Http-Method-Override';
    const X_POWERED_BY = 'X-Powered-By';
    const X_REQUEST_ID = 'X-Request-ID';
    const X_REQUESTED_WITH = 'X-Requested-With';
    const X_UA_COMPATIBLE = 'X-UA-Compatible';
    const X_UIDH = 'X-UIDH';
    const X_XSS_PROTECTION = 'X-XSS-Protection';
    const X_WAP_PROFILE = 'X-Wap-Profile';
    const X_WEBKIT_CSP = 'X-WebKit-CSP';

    /** @var string[] */
    private static $exceptions = [
        'et' => 'ET',
        'etag' => 'ETag',
        'te' => 'TE',
        'dnt' => 'DNT',
        'tsv' => 'TSV',
        'x-att-deviceid' => 'X-ATT-DeviceId',
        'x-correlation-id' => 'X-Correlation-ID',
        'x-request-id' => 'X-Request-ID',
        'x-ua-compatible' => 'X-UA-Compatible',
        'x-uidh' => 'X-UIDH',
        'x-xss-protection' => 'X-XSS-Protection',
        'x-webkit-csp' => 'X-WebKit-CSP',
        'www-authenticate' => 'WWW-Authenticate',
    ];

    public static function normalizeName(string $name): string
    {
        $name = strtolower($name);

        if (isset(self::$exceptions[$name])) {
            return self::$exceptions[$name];
        }

        return implode('-', array_map('ucfirst', explode('-', $name)));
    }

    public static function validateValue(&$value): bool
    {
        $value = self::normalizeName($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return '(?:X-)?[A-Z][a-z]+(?:[A-Z][a-z]+)*|' . implode('|', self::$exceptions);
    }

}
