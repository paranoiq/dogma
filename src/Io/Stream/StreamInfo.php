<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Stream;

use Dogma\Http\HttpHeaderParser;
use Dogma\StrictBehaviorMixin;

class StreamInfo
{
    use StrictBehaviorMixin;

    /** @var string */
    public $uri;

    /** @var string */
    public $wrapper;

    /** @var mixed */
    public $wrapperData;

    /** @var string */
    public $streamType;

    /** @var string */
    public $mode;

    /** @var string[] */
    public $filters;

    /** @var int */
    public $unreadBytes;

    /** @var bool */
    public $seekable;

    /** @var bool */
    public $timedOut;

    /** @var bool */
    public $blocked;

    /** @var bool */
    public $eof;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->uri = $data['uri'];
        $this->wrapper = $data['wrapper_type'];
        $this->streamType = $data['stream_type'];
        $this->mode = $data['mode'];
        $this->filters = $data['filters'] ?? [];
        $this->unreadBytes = (int) $data['unread_bytes'];
        $this->seekable = (bool) $data['seekable'];
        $this->timedOut = (bool) $data['timed_out'];
        $this->blocked = (bool) $data['blocked'];
        $this->eof = (bool) $data['eof'];
    }

    /**
     * @return mixed[]
     */
    public function getHttpHeaders(): array
    {
        return $this->wrapper === 'http'
            ? HttpHeaderParser::parseHeaders($this->wrapperData['wrapper_data'])
            : [];
    }

    /*
    [
        [wrapper_data] => [
            [0] => HTTP/1.1 200 OK
            [1] => Server: Apache/2.2.3 (Red Hat)
            [2] => Last-Modified: Tue, 15 Nov 2005 13:24:10 GMT
            [3] => ETag: "b300b4-1b6-4059a80bfd280"
            [4] => Accept-Ranges: bytes
            [5] => Content-Type: text/html; charset=UTF-8
            [6] => Set-Cookie: FOO=BAR; expires=Fri, 21-Dec-2012 12:00:00 GMT; path=/; domain=.example.com
            [6] => Connection: close
            [7] => Date: Fri, 16 Oct 2009 12:00:00 GMT
            [8] => Age: 1164
            [9] => Content-Length: 438
        ]
        [wrapper_type] => http
        [stream_type] => tcp_socket/ssl
        [mode] => r
        [unread_bytes] => 438
        [seekable] =>
        [uri] => http://www.example.com/
        [timed_out] =>
        [blocked] => 1
        [eof] =>
    ]
    */

}
