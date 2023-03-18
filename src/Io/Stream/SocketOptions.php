<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Stream;

use Dogma\StrictBehaviorMixin;

class SocketOptions
{
    use StrictBehaviorMixin;

    public const BIND_TO = 'bindto';
    public const TCP_NO_DELAY = 'tcp_nodelay';

    // stream_socket_server()
    public const BACKLOG = 'backlog';
    public const IPV6_ONLY = 'ipv6_v6only';
    public const REUSE_PORT = 'so_reuseport';
    public const BROADCAST = 'so_broadcast';

}
