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

    /**
     * @return string[]
     */
    public static function getNames(): array
    {
        return [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];
    }

    /**
     * @return string[]
     */
    public static function getShortcuts(): array
    {
        return [
            'mon',
            'tue',
            'wed',
            'thu',
            'fri',
            'sat',
            'sun',
        ];
    }

}
