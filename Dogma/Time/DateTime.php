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
     * @param \DateTimeZone $timeZone
     * @return static
     */
    public static function createFromFormat($format, $timeString, $timeZone = null)
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

    /**
     * @param integer $timestamp
     * @param \DateTimeZone $timeZone
     * @return static
     */
    public static function createFromTimestamp($timestamp, DateTimeZone $timeZone = null)
    {
        Check::integer($timestamp);

        return static::createFromFormat('U', $timestamp, $timeZone);
    }

    /**
     * @p(8aram \DateTimeInterface $dateTime
     * @param \DateTimeZone $timeZone
     * @return static
     */
    public static function createFromDateTimeInterface(DateTimeInterface $dateTime, DateTimeZone $timeZone = null)
    {
        if ($timeZone === null) {
            $timeZone = $dateTime->getTimezone();
        }
        return new static($dateTime->format(self::DEFAULT_FORMAT), $timeZone);
    }

    /**
     * @param \Dogma\Time\Date $date
     * @param \Dogma\Time\Time $time
     * @param \DateTimeZone|null $timeZone
     * @return static
     */
    public static function createFromDateAndTime(Date $date, Time $time, DateTimeZone $timeZone = null)
    {
        return new static($date->format(Date::DEFAULT_FORMAT) . ' ' . $time->format(Time::DEFAULT_FORMAT), $timeZone);
    }

    /**
     * @param string $format
     * @return string
     */
    public function format($format = self::DEFAULT_FORMAT)
    {
        return parent::format($format);
    }

    /**
     * @return \Dogma\Time\Date
     */
    public function getDate()
    {
        return new Date($this->format(Date::DEFAULT_FORMAT));
    }

    /**
     * @return \Dogma\Time\Time
     */
    public function getTime()
    {
        return new Time($this->format(Time::DEFAULT_FORMAT));
    }

    /**
     * @param \Dogma\Time\Time|integer $time|$hours
     * @param integer|null $minutes
     * @param integer|null $seconds
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

    /**
     * @param \DateTimeInterface $dateTime
     * @return integer
     */
    public function compare(DateTimeInterface $dateTime)
    {
        return $this > $dateTime ? 1 : ($dateTime > $this ? -1 : 0);
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return boolean
     */
    public function isEqual(DateTimeInterface $dateTime)
    {
        return $this->getTimestamp() === $dateTime->getTimestamp();
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return boolean
     */
    public function isBefore(DateTimeInterface $dateTime)
    {
        return $this < $dateTime;
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return boolean
     */
    public function isAfter(DateTimeInterface $dateTime)
    {
        return $this > $dateTime;
    }

    /**
     * @param \DateTimeInterface $sinceTime
     * @param \DateTimeInterface $untilTime
     * @return boolean
     */
    public function isBetween(DateTimeInterface $sinceTime, DateTimeInterface $untilTime)
    {
        return $this >= $sinceTime && $this <= $untilTime;
    }

    /**
     * @return boolean
     */
    public function isFuture()
    {
        return $this > new self;
    }

    /**
     * @return boolean
     */
    public function isPast()
    {
        return $this < new self;
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return boolean
     */
    public function isSameDay($date)
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) === $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return boolean
     */
    public function isBeforeDay($date)
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) < $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return boolean
     */
    public function isAfterDay($date)
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) > $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $sinceDate
     * @param \DateTimeInterface|\Dogma\Time\Date $untilDate
     * @return boolean
     */
    public function isBetweenDays($sinceDate, $untilDate)
    {
        Check::types($sinceDate, [DateTimeInterface::class, Date::class]);
        Check::types($untilDate, [DateTimeInterface::class, Date::class]);

        $thisDate = $this->format(Date::DEFAULT_FORMAT);

        return $thisDate >= $sinceDate->format(Date::DEFAULT_FORMAT)
            && $thisDate <= $untilDate->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @return boolean
     */
    public function isToday()
    {
        return $this->isBetween(new static('today'), new static('tomorrow -1 second'));
    }

    /**
     * @return boolean
     */
    public function isYesterday()
    {
        return $this->isBetween(new static('yesterday'), new static('today -1 second'));
    }

    /**
     * @return boolean
     */
    public function isTomorrow()
    {
        return $this->isBetween(new static('tomorrow'), new static('tomorrow +1 day -1 second'));
    }

}
