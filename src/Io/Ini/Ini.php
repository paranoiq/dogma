<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\Check;
use Dogma\StaticClassMixin;
use Dogma\Str;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Time;
use Dogma\Time\TimeZone;
use const INI_SCANNER_NORMAL;
use const INI_SCANNER_TYPED;
use const PHP_INT_MAX;
use function ctype_digit;
use function error_clear_last;
use function error_get_last;
use function is_array;
use function is_numeric;
use function parse_ini_file;
use function parse_ini_string;
use function strtolower;
use function trim;

class Ini
{
    use StaticClassMixin;

    public const PARSE_NONE = INI_SCANNER_NORMAL;
    public const PARSE_SCALARS = INI_SCANNER_TYPED;

    /** @see ValueParser::parse() */
    public const PARSE_OBJECTS = 42;

    /**
     * @param string|Path $file
     * @param bool $sections
     * @param int $mode
     * @return mixed[]
     */
    public static function parseFile($file, bool $sections = true, int $mode = self::PARSE_SCALARS): array
    {
        Check::enum($mode, [self::PARSE_NONE, self::PARSE_SCALARS, self::PARSE_OBJECTS]);

        if ($file instanceof Path) {
            $file = $file->getPath();
        }

        error_clear_last();
        $values = @parse_ini_file($file, $sections, $mode ? INI_SCANNER_NORMAL : INI_SCANNER_TYPED);
        if ($values === false) {
            $error = error_get_last();
            if ($error === null) {
                throw new IniException('Unknown error when parsing file ' . $file . '.');
            } else {
                throw new IniException('Error when parsing ini file ' . $file . ': ' . $error['message']);
            }
        }

        return $mode === self::PARSE_OBJECTS
            ? self::parseAll($values)
            : $values;
    }

    /**
     * @param string $string
     * @param bool $sections
     * @param int $mode
     * @return mixed[]
     */
    public static function parseString(string $string, bool $sections = true, int $mode = self::PARSE_SCALARS): array
    {
        Check::enum($mode, [self::PARSE_NONE, self::PARSE_SCALARS, self::PARSE_OBJECTS]);

        error_clear_last();
        $values = @parse_ini_string($string, $sections, $mode ? INI_SCANNER_NORMAL : INI_SCANNER_TYPED);
        if ($values === false) {
            $error = error_get_last();
            if ($error === null) {
                throw new IniException('Unknown error when parsing ini string.');
            } else {
                throw new IniException('Error when parsing ini string: ' . $error['message']);
            }
        }

        return $mode === self::PARSE_OBJECTS
            ? self::parseAll($values)
            : $values;
    }

    /**
     * @param mixed[] $values
     * @return mixed[]
     */
    private static function parseAll(array $values): array
    {
        foreach ($values as $i => $value) {
            if (is_array($value)) {
                $values[$i] = self::parseAll($value);
            } else {
                $values[$i] = self::parse($value);
            }
        }

        return $values;
    }

    /**
     * When $parseObjects is set, in addition to scalars (bool, int, float) detects and returns these types:
     * - Date
     * - Time
     * - DateTime
     * - TimeZone
     *
     * @param string $value
     * @return mixed
     */
    private static function parse(string $value)
    {
        $lower = strtolower($value);

        if ($lower === 'true') {
            return true;
        } elseif ($lower === 'false') {
            return false;
        } elseif ($lower === 'null') {
            return null;
        } elseif (is_numeric($value)) {
            $float = (float) $value;

            $negative = $float < 0;
            $numbers = trim($value, " \t+-.");
            if (ctype_digit($numbers)) {
                $int = (int) $value;
                if ($int !== PHP_INT_MAX) {
                    return $negative ? -$int : $int;
                }
            }

            if ($float === (float) (int) $float) {
                return (int) $float;
            }

            return $float;
        }

        $trimmed = trim($value);
        if (Str::match($trimmed, '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/')) {
            return new Date($trimmed);
        } elseif (Str::match($trimmed, '/^[0-2][0-9]:[0-5][0-9](:[0-5][0-9](.[0-9]+)?)?$/')) {
            return new Time($trimmed);
        } elseif (Str::match($trimmed, '/^[0-9]{4}-[0-9]{2}-[0-9]{2}[ T][0-2][0-9]:[0-5][0-9]/')) {
            return new DateTime($trimmed);
        } elseif (TimeZone::isValid($trimmed)) {
            return new TimeZone($trimmed);
        }

        return $value;
    }

}
