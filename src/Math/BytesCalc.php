<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\InvalidValueException;
use Dogma\StaticClassMixin;
use Dogma\Str;
use function abs;
use function array_search;
use function pow;
use function round;

class BytesCalc
{
    use StaticClassMixin;

    private const UNITS = ['', 'k', 'M', 'G', 'T', 'P', 'E'];

    public static function parse(string $value): int
    {
        $match = Str::match($value, '/^(\\d+(?:\\.\\d+))\\s*([kMGTPE](i?B)?)?$/i');
        if (!$match) {
            throw new InvalidValueException($value, 'bytes');
        }

        $unit = trim($match[2], 'iIbB');

        return intval($match[1]) * pow(1024, array_search($unit, self::UNITS));
    }

    public static function format(float $bytes, int $precision = 2): string
    {
        $bytes = round($bytes);
        foreach (self::UNITS as $i => $unit) {
            if (abs($bytes) < 1024 || $i === 6) {
                break;
            }
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $unit . 'B';
    }

    public static function formatIni(float $bytes): string
    {
        $bytes = round($bytes);
        foreach (self::UNITS as $i => $unit) {
            if (abs($bytes) < 1024 || $i === 6) {
                break;
            }
            $bytes /= 1024;
        }

        return round($bytes) . ' ' . $unit;
    }

}
