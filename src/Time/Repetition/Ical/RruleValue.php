<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: WKST BYMONTH BYWEEKNO BYYEARDAY BYMONTHDAY BYDAY BYHOUR BYMINUTE BYSECOND BYSETPOS
// spell-check-ignore: EASTER BYEASTER BYHOLIDAY BYMOONPHASE BYSUMMERTIME BYYEARCYCLE BYDAYCYCLE

namespace Dogma\Time\Repetition\Ical;

use Dogma\Enum\StringEnum;

class RruleValue extends StringEnum
{

    public const FREQUENCY = 'FREQ';
    public const UNTIL = 'UNTIL';
    public const COUNT = 'COUNT';
    public const INTERVAL = 'INTERVAL';
    public const WEEK_START = 'WKST';

    public const BY_MONTH = 'BYMONTH';
    public const BY_WEEK = 'BYWEEKNO';
    public const BY_YEAR_DAY = 'BYYEARDAY';
    public const BY_MONTH_DAY = 'BYMONTHDAY';
    public const BY_WEEK_DAY = 'BYDAY';
    public const BY_HOUR = 'BYHOUR';
    public const BY_MINUTE = 'BYMINUTE';
    public const BY_SECOND = 'BYSECOND';
    public const BY_SET_POSITION = 'BYSETPOS';

    // date
    public const BY_EASTER = 'BYEASTER'; // sunday/monday
    public const BY_HOLIDAY = 'BYHOLIDAY'; // name, country
    public const BY_MOON_PHASE = 'BYMOONPHASE';
    public const BY_SUMMER_TIME = 'BYSUMMERTIME'; // start/end, country
    public const BY_YEAR_CYCLE = 'BYYEARCYCLE';

    // datetime
    public const BY_DAY_CYCLE = 'BYDAYCYCLE'; // gps

}
