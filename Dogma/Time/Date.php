<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use DateTimeInterface;
use DateTimeZone;
use Dogma\Check;

/**
 * Date class.
 */
class Date implements \Dogma\NonIterable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;
    use \Dogma\Time\DateDateTimeCommonMixin;

    const DEFAULT_FORMAT = 'Y-m-d';

    /** @var \DateTime */
    private $dateTime;

    public function __construct(string $dateString = 'today 00:00:00')
    {
        try {
            $this->dateTime = new \DateTime($dateString);
        } catch (\Throwable $e) {
            throw new \Dogma\Time\InvalidDateTimeException($e);
        }
    }

    public static function createFromTimestamp(int $timestamp): Date
    {
        return DateTime::createFromTimestamp($timestamp)->getDate();
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime): Date
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->getDate();
        } else {
            return DateTime::createFromDateTimeInterface($dateTime)->getDate();
        }
    }

    public static function createFromComponents(int $year, int $month, int $day)
    {
        Check::range($year, 1, 9999);
        Check::range($month, 1, 12);
        Check::range($day, 1, 31);

        return new static(sprintf('%d-%d-%d 00:00:00', $year, $month, $day));
    }

    public function __clone()
    {
        $this->dateTime = clone($this->dateTime);
    }

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->dateTime->format($format);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @param bool $absolute
     */
    public function diff($date, bool $absolute = false)
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return (new \DateTime($this->format()))->diff(new \DateTime($date->format(self::DEFAULT_FORMAT)), $absolute);
    }

    public function getStart(DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(0, 0, 0);
    }

    public function getStartFormatted(string $format = null, DateTimeZone $timeZone = null): string
    {
        return $this->getStart($timeZone)->format($format ?? DateTime::DEFAULT_FORMAT);
    }

    public function getEnd(DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(23, 59, 59);
    }

    public function getEndFormatted(string $format = null, DateTimeZone $timeZone = null): string
    {
        return $this->getStart($timeZone)->setTime(23, 59, 59)->format($format ?? DateTime::DEFAULT_FORMAT);
    }

    public function compare(Date $date): int
    {
        return $this->isAfter($date) ? 1 : ($this->isBefore($date) ? -1 : 0);
    }

    public function isEqual(Date $date): bool
    {
        return $this->format() === $date->format();
    }

    public function isBefore(Date $date): bool
    {
        return $this->format() < $date->format();
    }

    public function isAfter(Date $date): bool
    {
        return $this->format() > $date->format();
    }

    public function isBetween(Date $sinceDate, Date $untilDate): bool
    {
        $thisDate = $this->format();

        return $thisDate >= $sinceDate->format() && $thisDate <= $untilDate->format();
    }

    public function isFuture(TimeProvider $timeProvider = null): bool
    {
        return $this->format() > ($timeProvider !== null ? $timeProvider->getDate() : new Date('today'))->format();
    }

    public function isPast(TimeProvider $timeProvider = null): bool
    {
        return $this->format() < ($timeProvider !== null ? $timeProvider->getDate() : new Date('today'))->format();
    }

}
