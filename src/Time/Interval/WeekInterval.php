<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\Check;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DayOfWeek;
use Dogma\Time\InvalidWeekIntervalException;
use Dogma\Time\Microseconds;

/**
 * DateTimeInterval aligned to a single week boundaries.
 * Week always starts with monday and ends with sunday.
 * WeekInterval cannot be empty.
 */
class WeekInterval extends DateTimeInterval
{

    public function __construct(DateTime $start, DateTime $end, bool $openStart = false, bool $openEnd = true)
    {
        if ($start->getDayOfWeek() !== DayOfWeek::MONDAY) {
            throw new InvalidWeekIntervalException($start, $end);
        } elseif ($start->getTime()->getMicroTime() !== 0) {
            throw new InvalidWeekIntervalException($start, $end);
        } elseif ($start->difference($end)->getMicrosecondsTotal() !== Microseconds::WEEK) {
            throw new InvalidWeekIntervalException($start, $end);
        }

        parent::__construct($start, $end, $openStart, $openEnd);
    }

    public static function createFromDate(Date $date): self
    {
        return self::createFromIsoYearAndWeek((int) $date->format('o'), (int) $date->format('W'));
    }

    public static function createFromDateTime(DateTime $dateTime): self
    {
        return self::createFromIsoYearAndWeek((int) $dateTime->format('o'), (int) $dateTime->format('W'));
    }

    public static function createFromIsoYearAndWeek(int $year, int $week, ?\DateTimeZone $timeZone = null): self
    {
        Check::range($year, 0, 9999);
        Check::range($week, 1, 53);

        $dateTime = new \DateTime('today 00:00:00', $timeZone);
        $dateTime->setISODate($year, $week);
        $start = DateTime::createFromDateTimeInterface($dateTime, $timeZone);
        $end = $start->modify('+ 1 week');

        return new static($start, $end, false, true);
    }

    /**
     * @param \Dogma\Time\Interval\DateTimeInterval $interval
     * @return self[]
     */
    public static function createOverlappingIntervals(DateTimeInterval $interval): array
    {
        if ($interval->isEmpty()) {
            return [];
        }

        $intervals = [];
        $current = $interval->getStart();
        do {
            $intervals[] = self::createFromIsoYearAndWeek((int) $current->format('o'), (int) $current->format('W'));
            $current = $current->modify('+ 1 week');
        } while ($current < $interval->getEnd());

        return $intervals;
    }

    public function next(): self
    {
        return self::shift('+ 1 week');
    }

    public function previous(): self
    {
        return self::shift('- 1 week');
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
