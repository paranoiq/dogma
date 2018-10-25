<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\StaticClassMixin;
use function abs;
use function ceil;
use function floor;

class IntCalc
{
    use StaticClassMixin;

    public static function roundTo(int $number, int $multiple): int
    {
        $up = self::roundUpTo($number, $multiple);
        $down = self::roundDownTo($number, $multiple);

        return abs($up - $number) > abs($number - $down) ? $down : $up;
    }

    public static function roundDownTo(int $number, int $multiple): int
    {
        $multiple = abs($multiple);

        return (int) (floor($number / $multiple) * $multiple);
    }

    public static function roundUpTo(int $number, int $multiple): int
    {
        $multiple = abs($multiple);

        return (int) (ceil($number / $multiple) * $multiple);
    }

}
