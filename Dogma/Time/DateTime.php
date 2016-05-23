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
 * Immutable date and time class
 */
class DateTime extends \DateTimeImmutable implements \Dogma\NonIterable, \DateTimeInterface
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;

    const DEFAULT_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param string $format
     * @param string $timeString
     * @param \DateTimeZone|null $timeZone
     * @return \Dogma\Time\DateTime
     */
    public static function createFromFormat($format, $timeString, $timeZone = null): self
    {
        // due to invalid typehint in parent class...
        Check::nullableObject($timeZone, DateTimeZone::class);

        // due to invalid optional arguments handling...
        if ($timeZone === null) {
            $dateTime = parent::createFromFormat($format, $timeString);
        } else {
            $dateTime = parent::createFromFormat($format, $timeString, $timeZone);
        }

        return new static($dateTime->format(self::DEFAULT_FORMAT), $timeZone);
    }

    public static function createFromTimestamp(int $timestamp, DateTimeZone $timeZone = null): self
    {
        Check::integer($timestamp);

        return static::createFromFormat('U', $timestamp, $timeZone);
    }

    public static function createFromDateTimeInterface(DateTimeInterface $dateTime, DateTimeZone $timeZone = null): self
    {
        if ($timeZone === null) {
            $timeZone = $dateTime->getTimezone();
        }
        return new static($dateTime->format(self::DEFAULT_FORMAT), $timeZone);
    }

    public static function createFromDateAndTime(Date $date, Time $time, DateTimeZone $timeZone = null): self
    {
        return new static($date->format(Date::DEFAULT_FORMAT) . ' ' . $time->format(Time::DEFAULT_FORMAT), $timeZone);
    }

    /**
     * @param string $format
     * @return string
     */
    public function format($format = self::DEFAULT_FORMAT): string
    {
        return parent::format($format);
    }

    public function getDate(): Date
    {
        return new Date($this->format(Date::DEFAULT_FORMAT));
    }

    public function getTime(): Time
    {
        return new Time($this->format(Time::DEFAULT_FORMAT));
    }

    /**
     * @param \Dogma\Time\Time|int $time|$hours
     * @param int|null $minutes
     * @param int|null $seconds
     * @return bool|\DateTimeImmutable
     */
    public function setTime($time, $minutes = null, $seconds = null)
    {
        if ($time instanceof Time) {
            return parent::setTime($time->getHours(), $time->getMinutes(), $time->getSeconds());
        } else {
            return parent::setTime($time, $minutes, $seconds);
        }
    }

    public function compare(DateTimeInterface $dateTime): int
    {
        return $this > $dateTime ? 1 : ($dateTime > $this ? -1 : 0);
    }

    public function isEqual(DateTimeInterface $dateTime): bool
    {
        return $this->getTimestamp() === $dateTime->getTimestamp();
    }

    public function isBefore(DateTimeInterface $dateTime): bool
    {
        return $this < $dateTime;
    }

    public function isAfter(DateTimeInterface $dateTime): bool
    {
        return $this > $dateTime;
    }

    public function isBetween(DateTimeInterface $sinceTime, DateTimeInterface $untilTime): bool
    {
        return $this >= $sinceTime && $this <= $untilTime;
    }

    public function isFuture(): bool
    {
        return $this > new self;
    }

    public function isPast(): bool
    {
        return $this < new self;
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return bool
     */
    public function isSameDay($date): bool
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) === $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return bool
     */
    public function isBeforeDay($date): bool
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) < $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return bool
     */
    public function isAfterDay($date): bool
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) > $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $sinceDate
     * @param \DateTimeInterface|\Dogma\Time\Date $untilDate
     * @return bool
     */
    public function isBetweenDays($sinceDate, $untilDate): bool
    {
        Check::types($sinceDate, [DateTimeInterface::class, Date::class]);
        Check::types($untilDate, [DateTimeInterface::class, Date::class]);

        $thisDate = $this->format(Date::DEFAULT_FORMAT);

        return $thisDate >= $sinceDate->format(Date::DEFAULT_FORMAT)
            && $thisDate <= $untilDate->format(Date::DEFAULT_FORMAT);
    }

    public function isToday(): bool
    {
        return $this->isBetween(new static('today'), new static('tomorrow -1 second'));
    }

    public function isYesterday(): bool
    {
        return $this->isBetween(new static('yesterday'), new static('today -1 second'));
    }

    public function isTomorrow(): bool
    {
        return $this->isBetween(new static('tomorrow'), new static('tomorrow +1 day -1 second'));
    }

}
