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

class SslOptions
{
    use StaticClassMixin;

    public const PEER_NAME = 'peer_name';
    public const VERIFY_PEER = 'verify_peer'; // bool true
    public const VERIFY_DEPTH = 'verify_depth'; // int 0
    public const VERIFY_PEER_NAME = 'verify_peer_name'; // bool true
    public const ALLOW_SELF_SIGNED = 'allow_self_signed'; // bool false
    public const CAPTURE_PEER_CERT = 'capture_peer_cert'; // bool false
    public const CAPTURE_PEER_CERT_CHAIN = 'capture_peer_cert_chain'; // bool false
    public const SNI_ENABLED = 'SNI_enabled'; // bool
    public const PEER_FINGERPRINT = 'peer_fingerprint'; // string

    public const CA_FILE = 'cafile'; // string file
    public const CA_PATH = 'capath'; // string dir
    public const LOCAL_CERT = 'local_cert'; // string file
    public const LOCAL_CERT_PASSWORD = 'passphrase'; // string
    public const LOCAL_PK = 'local_pk'; // string file

    public const CIPHERS = 'ciphers'; // string DEFAULT

    public const DISABLE_COMPRESSION = 'disable_compression'; // bool

    public const SECURITY_LEVEL = 'security_level'; // int (0 SSL2+, 1 SSL3+, 2 TLS1.0+, 3 TLS1.1+, 4 TLS1.2+, 5 ...)

}
