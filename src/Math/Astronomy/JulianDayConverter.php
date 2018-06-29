<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Astronomy;

use Dogma\StaticClassMixin;
use Dogma\Time\Seconds;

class JulianDayConverter
{
    use StaticClassMixin;

    // count of days since noon on Monday, January 1, 4713 BC, proleptic Julian calendar to January 1, 1970
    private const JULIAN_TIMESTAMP_EPOCH = 2440587.5;

    public static function timestampToJulianDay(int $timestamp): float
    {
        return $timestamp / Seconds::DAY + self::JULIAN_TIMESTAMP_EPOCH;
    }

    public static function julianDayToTimestamp(float $day): int
    {
        return (int) ($day - self::JULIAN_TIMESTAMP_EPOCH) * Seconds::DAY;
    }

}
