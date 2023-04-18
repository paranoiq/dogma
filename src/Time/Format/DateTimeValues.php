<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use DateTimeZone;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateOrTime;

class DateTimeValues
{
    use StrictBehaviorMixin;

    public int $year;

    public bool $leapYear;

    public int $dayOfYear;

    public int $quarter;

    public int $month;

    public int $weekOfYear;

    public int $isoWeekYear;

    public int $dayOfWeek;

    public int $day;

    public int $hours;

    public int $minutes;

    public int $seconds;

    public int $miliseconds;

    public int $microseconds;

    public bool $dst;

    public string $offset;

    public DateTimeZone $timezone;

    public DateOrTime $dateTime;

    private function __construct(DateOrTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    public function create(DateOrTime $dateTime): self
    {
        $self = new self($dateTime);

        $dateTime->fillValues($self);

        return $self;
    }

}
