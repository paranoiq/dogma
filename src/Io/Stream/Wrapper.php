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

class Wrapper
{
    use StaticClassMixin;

    public const SOCKET = 'socket';
    public const HTTP = 'http';
    public const FTP = 'ftp';
    public const SSL = 'ssl';
    public const PHAR = 'phar';
    public const ZIP = 'zip';

}
