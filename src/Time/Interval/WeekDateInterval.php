<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use DateTimeInterface;
use Dogma\Time\Date;
use Dogma\Time\DayOfWeek;
use Dogma\Time\InvalidWeekDateIntervalException;

/**
 * DateInterval aligned to a single week boundaries.
 * Week always starts with monday and ends with sunday.
 * WeekInterval cannot be empty.
 */
class WeekDateInterval extends DateInterval
{

    public static function validate(Date $start, Date $end): void
    {
        parent::validate($start, $end);

        if ($start->getDayOfWeek() !== DayOfWeek::MONDAY) {
            throw new InvalidWeekDateIntervalException($start, $end);
        } elseif ($start->difference($end)->getDaysTotal() !== 6) {
            throw new InvalidWeekDateIntervalException($start, $end);
        }
    }

    public static function create(Date $start, Date $end): static
    {
        return new static($start, $end);
    }

    public static function createFromDate(Date $date): static
    {
        return self::createFromIsoYearAndWeek((int) $date->format('o'), (int) $date->format('W'));
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): static
    {
        return self::createFromIsoYearAndWeek((int) $dateTime->format('o'), (int) $dateTime->format('W'));
    }

    public static function createFromIsoYearAndWeek(int $year, int $week): static
    {
        $start = Date::createFromIsoYearAndWeek($year, $week, DayOfWeek::MONDAY);
        $end = $start->modify('+ 6 days');

        return new static($start, $end);
    }

    /**
     * @return array<static>
     */
    public static function createOverlappingIntervals(DateInterval $interval): array
    {
        if ($interval->isEmpty()) {
            return [];
        }

        $intervals = [];
        $current = $interval->getStart();
        do {
            $intervals[] = self::createFromIsoYearAndWeek((int) $current->format('o'), (int) $current->format('W'));
            $current = $current->modify('+ 1 week');
        } while ($current <= $interval->getEnd());

        return $intervals;
    }

    public function next(): static
    {
        return $this->shift('+ 1 week');
    }

    public function previous(): static
    {
        return $this->shift('- 1 week');
    }

    public function getIsoYear(): int
    {
        return (int) $this->getStart()->format('o');
    }

    public function getIsoWeek(): int
    {
        return (int) $this->getStart()->format('W');
    }

}
