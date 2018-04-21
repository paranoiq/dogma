<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Enum\IntEnum;

class Month extends IntEnum
{

    public const JANUARY = 1;
    public const FEBRUARY = 2;
    public const MARCH = 3;
    public const APRIL = 4;
    public const MAY = 5;
    public const JUNE = 6;
    public const JULY = 7;
    public const AUGUST = 8;
    public const SEPTEMBER = 9;
    public const OCTOBER = 10;
    public const NOVEMBER = 11;
    public const DECEMBER = 12;

    /**
     * @return string[]
     */
    public static function getNames(): array
    {
        return [
            self::JANUARY => 'january',
            self::FEBRUARY => 'february',
            self::MARCH => 'march',
            self::APRIL => 'april',
            self::MAY => 'may',
            self::JUNE => 'june',
            self::JULY => 'july',
            self::AUGUST => 'august',
            self::SEPTEMBER => 'september',
            self::OCTOBER => 'october',
            self::NOVEMBER => 'november',
            self::DECEMBER => 'december',
        ];
    }

    /**
     * @return string[]
     */
    public static function getShortcuts(): array
    {
        return [
            self::JANUARY => 'jan',
            self::FEBRUARY => 'feb',
            self::MARCH => 'mar',
            self::APRIL => 'apr',
            self::MAY => 'may',
            self::JUNE => 'jun',
            self::JULY => 'jul',
            self::AUGUST => 'aug',
            self::SEPTEMBER => 'sep',
            self::OCTOBER => 'oct',
            self::NOVEMBER => 'nov',
            self::DECEMBER => 'dec',
        ];
    }

}
