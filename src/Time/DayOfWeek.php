<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: mon tue thu fri

namespace Dogma\Time;

use Dogma\Enum\IntEnum;

/**
 * Day of week as defined in ISO-8601 (1 for Monday through 7 for Sunday)
 */
class DayOfWeek extends IntEnum
{

    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;
    public const SUNDAY = 7;

    public static function monday(): self
    {
        return self::get(self::MONDAY);
    }

    public static function tuesday(): self
    {
        return self::get(self::TUESDAY);
    }

    public static function wednesday(): self
    {
        return self::get(self::WEDNESDAY);
    }

    public static function thursday(): self
    {
        return self::get(self::THURSDAY);
    }

    public static function friday(): self
    {
        return self::get(self::FRIDAY);
    }

    public static function saturday(): self
    {
        return self::get(self::SATURDAY);
    }

    public static function sunday(): self
    {
        return self::get(self::SUNDAY);
    }

    /**
     * @return string[]
     */
    public static function getNames(): array
    {
        return [
            self::MONDAY => 'monday',
            self::TUESDAY => 'tuesday',
            self::WEDNESDAY => 'wednesday',
            self::THURSDAY => 'thursday',
            self::FRIDAY => 'friday',
            self::SATURDAY => 'saturday',
            self::SUNDAY => 'sunday',
        ];
    }

    /**
     * @return string[]
     */
    public static function getShortcuts(): array
    {
        return [
            self::MONDAY => 'mon',
            self::TUESDAY => 'tue',
            self::WEDNESDAY => 'wed',
            self::THURSDAY => 'thu',
            self::FRIDAY => 'fri',
            self::SATURDAY => 'sat',
            self::SUNDAY => 'sun',
        ];
    }

    public function getName(): string
    {
        return self::getNames()[$this->getValue()];
    }

    public function getShortcut(): string
    {
        return self::getShortcuts()[$this->getValue()];
    }

    public function isWeekend(): bool
    {
        $value = $this->getValue();

        return $value === self::SATURDAY || $value === self::SUNDAY;
    }

}
