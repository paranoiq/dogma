<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: jan feb apr jul aug sep oct nov dec

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

    /**
     * @param bool $leapYear
     * @return int[]
     */
    public static function getLengths(bool $leapYear = false): array
    {
        return $leapYear
            ? [
                self::JANUARY => 31,
                self::FEBRUARY => 29,
                self::MARCH => 31,
                self::APRIL => 30,
                self::MAY => 31,
                self::JUNE => 30,
                self::JULY => 31,
                self::AUGUST => 31,
                self::SEPTEMBER => 30,
                self::OCTOBER => 31,
                self::NOVEMBER => 30,
                self::DECEMBER => 31,
            ] : [
                self::JANUARY => 31,
                self::FEBRUARY => 28,
                self::MARCH => 31,
                self::APRIL => 30,
                self::MAY => 31,
                self::JUNE => 30,
                self::JULY => 31,
                self::AUGUST => 31,
                self::SEPTEMBER => 30,
                self::OCTOBER => 31,
                self::NOVEMBER => 30,
                self::DECEMBER => 31,
            ];
    }

    public function getName(): string
    {
        return self::getNames()[$this->getValue()];
    }

    public function getShortcut(): string
    {
        return self::getShortcuts()[$this->getName()];
    }

    public function getDays(bool $leapYear): int
    {
        return self::getDays($leapYear)[$this->getValue()];
    }

}
