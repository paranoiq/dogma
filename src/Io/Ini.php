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
use function error_clear_last;
use function error_get_last;
use function parse_ini_string;
use const INI_SCANNER_NORMAL;
use const INI_SCANNER_TYPED;
use function parse_ini_file;

class Ini
{
    use StaticClassMixin;

    public const PARSE_NONE = INI_SCANNER_NORMAL;
    public const PARSE_SCALARS = INI_SCANNER_TYPED;

    /**
     * In addition to PARSE_SCALARS detects and returns these types:
     * - Date
     * - Time
     * - DateTime
     * - TimeZone
     * - MimeType
     * - Charset
     * - Path
     * - PathList
     * - Url
     */
    public const PARSE_OBJECTS = 42;

    public static function parseFile(string $fileName, $sections = true, $mode = self::PARSE_SCALARS): array
    {
        error_clear_last();
        $values = parse_ini_file($fileName, $sections, $mode);
        if ($values === false) {
            $error = error_get_last();
            if ($error === null) {
                throw new IniException('Unknown error when parsing file ' . $fileName . '.');
            } else {
                throw new IniException('Error when parsing ini file ' . $fileName . ': ' . $error['message']);
            }
        }

        return self::normalize($values, $mode);
    }

    public static function parseString(string $string, $sections = true, $mode = self::PARSE_SCALARS): array
    {
        error_clear_last();
        $values = parse_ini_string($string, $sections, $mode);
        if ($values === false) {
            $error = error_get_last();
            if ($error === null) {
                throw new IniException('Unknown error when parsing ini string.');
            } else {
                throw new IniException('Error when parsing ini string: ' . $error['message']);
            }
        }

        return self::normalize($values, $mode);
    }

    public static function normalize(array $values, int $mode): array
    {
        // todo
    }

}
