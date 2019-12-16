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
use Dogma\Time\DateTime;
use Dogma\Time\DayOfWeek;
use Dogma\Time\DaysOfWeek;
use Dogma\Time\InvalidWeekDayHoursSetException;
use function ksort;

class WeekDayHoursSet implements Pokeable
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\Interval\WeekDayHours[] */
    private $weekDayHours = [];

    /**
     * @param \Dogma\Time\Interval\WeekDayHours[] $weekDayHoursList
     */
    public function __construct(array $weekDayHoursList)
    {
        foreach ($weekDayHoursList as $weekDayHours) {
            $day = $weekDayHours->getDay()->getValue();
            if (isset($this->weekDayHours[$day])) {
                throw new InvalidWeekDayHoursSetException($weekDayHours->getDay());
            }
            $this->weekDayHours[$day] = $weekDayHours;
        }
        ksort($this->weekDayHours);
    }

    public function createFromDaysOfWeekAndOpeningTime(DaysOfWeek $days, TimeInterval $opening, ?TimeInterval $break = null): self
    {
        $dayItems = [];
        foreach (DayOfWeek::getInstances() as $day) {
            if ($days->containsDay($day)) {
                $dayItems[] = WeekDayHours::createFromOpeningTime($day, $opening, $break);
            }
        }

        return new static($dayItems);
    }

    public function poke(): void
    {
        foreach ($this->weekDayHours as $hours) {
            $hours->poke();
        }
    }

    public function containsValue(DateTime $dateTime): bool
    {
        $date = $dateTime->getDate();

        return DateTimeIntervalSet::createFromDateIntervalAndWeekDayHoursSet(new DateInterval($date, $date), $this)->containsValue($dateTime);
    }

    public function containsInterval(DateTimeInterval $interval): bool
    {
        return DateTimeIntervalSet::createFromDateIntervalAndWeekDayHoursSet($interval->toDateInterval(), $this)->containsInterval($interval);
    }

    /**
     * @return \Dogma\Time\Interval\WeekDayHours[]
     */
    public function getWeekDayHours(): array
    {
        return $this->weekDayHours;
    }

    public function getByDay(DayOfWeek $dayOfWeek): ?WeekDayHours
    {
        $day = $dayOfWeek->getValue();

        return $this->getByDayNumber($day);
    }

    public function getByDayNumber(int $dayNumber): ?WeekDayHours
    {
        return $this->weekDayHours[$dayNumber] ?? null;
    }

}
