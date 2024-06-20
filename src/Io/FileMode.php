<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\StaticClassMixin;
use function in_array;

class FileMode
{
    use StaticClassMixin;

    // error if not found, keep content
    public const OPEN_READ = 'r';
    public const OPEN_READ_WRITE = 'r+';

    // error if found, no content
    public const CREATE_WRITE = 'x';
    public const CREATE_READ_WRITE = 'x+';

    // create if not found, keep content
    public const CREATE_OR_OPEN_WRITE = 'c';
    public const CREATE_OR_OPEN_READ_WRITE = 'c+';

    // create if not found, truncate content
    public const CREATE_OR_TRUNCATE_WRITE = 'w';
    public const CREATE_OR_TRUNCATE_READ_WRITE = 'w+';

    // create if not found, keep content, point to end of file, don't accept new position
    public const CREATE_OR_APPEND_WRITE = 'a';
    public const CREATE_OR_APPEND_READ_WRITE = 'a+';

    public static function getReopenMode(string $mode): string
    {
        if ($mode === self::CREATE_WRITE) {
            return self::CREATE_OR_OPEN_WRITE;
        } elseif ($mode === self::CREATE_READ_WRITE) {
            return self::CREATE_OR_OPEN_READ_WRITE;
        } else {
            return $mode;
        }
    }

    public static function isReadable(string $mode): bool
    {
        return $mode !== self::CREATE_WRITE
            && $mode !== self::CREATE_OR_OPEN_WRITE
            && $mode !== self::CREATE_OR_TRUNCATE_WRITE
            && $mode !== self::CREATE_OR_APPEND_WRITE;
    }

}
