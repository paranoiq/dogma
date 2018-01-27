<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

class DateTimeValues
{
    use \Dogma\StrictBehaviorMixin;

    public $year;
    public $leapYear;
    public $dayOfYear;
    public $quarter;
    public $month;
    public $daysInMonth;
    public $weekOfYear;
    public $isoWeekYear;
    public $dayOfWeek;
    public $day;

    public $hours;
    public $minutes;
    public $seconds;
    public $miliseconds;
    public $microseconds;

    public $timezone;
    public $dst;

    /** @var \DateTimeInterface|\Dogma\Time\Date|\Dogma\Time\Time */
    public $dateTime;

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date|\Dogma\Time\Time $dateTime
     */
    public function __construct($dateTime, array $values)
    {
        $this->dateTime = $dateTime;
        foreach ($values as $i => $v) {
            ///
        }
    }

}
