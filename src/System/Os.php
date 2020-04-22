<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Enum\StringEnum;
use const PHP_OS_FAMILY;

/**
 * PHP_OS_FAMILY values
 */
class Os extends StringEnum
{

    public const WINDOWS = 'Windows';
    public const BSD = 'BSD';
    public const DARWIN = 'Darwin';
    public const SOLARIS = 'Solaris';
    public const LINUX = 'Linux';
    public const UNKNOWN = 'Unknown';

    public static function isWindows(): bool
    {
        return PHP_OS_FAMILY === self::WINDOWS;
    }

}
