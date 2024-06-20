<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Stream;

use Dogma\StaticClassMixin;

class HttpOptions
{
    use StaticClassMixin;

    public const METHOD = 'method';
    public const HEADER = 'header';
    public const USER_AGENT = 'user_agent';
    public const CONTENT = 'content';
    public const PROXY = 'proxy'; // eg. tcp://proxy.example.com:5100
    public const REQUEST_FULL_URI = 'request_fulluri'; // bool false
    public const FOLLOW_LOCATION = 'follow_location'; // int 1
    public const MAX_REDIRECTS = 'max_redirects'; // int 20
    public const PROTOCOL_VERSION = 'protocol_version'; // float 1.0
    public const TIMEOUT = 'timeout'; // default_socket_timeout from php.ini
    public const IGNORE_ERRORS = 'ignore_errors'; // bool false

}
