<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DayOfWeek;

class WeekDayHours implements Pokeable
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\DayOfWeek */
    private $day;

    /** @var \Dogma\Time\Interval\TimeIntervalSet */
    private $hours;

    public function __construct(DayOfWeek $day, TimeIntervalSet $hours)
    {
        $this->day = $day;
        $this->hours = $hours;
    }

    public static function createFromOpeningTime(DayOfWeek $day, TimeInterval $opening, ?TimeInterval $break = null): self
    {
        return new static($day, $break !== null ? $opening->subtract($break) : new TimeIntervalSet([$opening]));
    }

    public function poke(): void
    {
        $this->hours->poke();
    }

    public function getDay(): DayOfWeek
    {
        return $this->day;
    }

    public function getTimeIntervalSet(): TimeIntervalSet
    {
        return $this->hours;
    }

    /**
     * @return \Dogma\Time\Interval\TimeInterval[]
     */
    public function getTimeIntervals(): array
    {
        return $this->hours->getIntervals();
    }

}
