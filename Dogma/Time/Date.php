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
use Dogma\Check;

/**
 * Date class.
 */
class Date implements \Dogma\NonIterable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;

    const DEFAULT_FORMAT = 'Y-m-d';

    /** @var \DateTime */
    private $dateTime;

    /**
     * @param string $dateString
     */
    public function __construct($dateString = 'today 00:00:00')
    {
        try {
            $this->dateTime = new \DateTime($dateString);
        } catch (\Exception $e) {
            throw new \Dogma\Time\InvalidDateTimeException($e);
        }
    }

    /**
     * @param integer $timestamp
     * @return \Dogma\Time\Date
     */
    public static function createFromTimestamp($timestamp)
    {
        return DateTime::createFromTimestamp($timestamp)->getDate();
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return \Dogma\Time\Date
     */
    public static function createFromDateTimeInterface(DateTimeInterface $dateTime)
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->getDate();
        } else {
            return DateTime::createFromDateTimeInterface($dateTime)->getDate();
        }
    }

    public function __clone()
    {
        $this->dateTime = clone($this->dateTime);
    }

    /**
     * @param string $format
     * @return string
     */
    public function format($format = self::DEFAULT_FORMAT)
    {
        return $this->dateTime->format($format);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @param boolean $absolute
     */
    public function diff($date, $absolute = false)
    {
        Check::types($date, [DateTimeInterface::class, Date::class]);

        return (new \DateTime($this->format()))->diff(new \DateTime($date->format(self::DEFAULT_FORMAT)), $absolute);
    }

    /**
     * @param \DateTimeZone|null $timeZone
     * @return integer
     */
    public function getMidnightTimestamp($timeZone = null)
    {
        return (new \DateTime($this->format(), $timeZone))->setTime(0, 0, 0)->getTimestamp();
    }

    /**
     * @param \Dogma\Time\Date $date
     * @return integer
     */
    public function compare(Date $date)
    {
        return $this->isAfter($date) ? 1 : ($this->isBefore($date) ? -1 : 0);
    }

    /**
     * @param \Dogma\Time\Date $date
     * @return boolean
     */
    public function isEqual(Date $date)
    {
        return $this->format() === $date->format();
    }

    /**
     * @param \Dogma\Time\Date $date
     * @return boolean
     */
    public function isBefore(Date $date)
    {
        return $this->format() < $date->format();
    }

    /**
     * @param \Dogma\Time\Date $date
     * @return boolean
     */
    public function isAfter(Date $date)
    {
        return $this->format() > $date->format();
    }

    /**
     * @param \Dogma\Time\Date $sinceDate
     * @param \Dogma\Time\Date $untilDate
     * @return boolean
     */
    public function isBetween(Date $sinceDate, Date $untilDate)
    {
        $thisDate = $this->format();

        return $thisDate >= $sinceDate->format() && $thisDate <= $untilDate->format();
    }

    /**
     * @return boolean
     */
    public function isFuture()
    {
        return $this->format() > (new static('today'))->format();
    }

    /**
     * @return boolean
     */
    public function isPast()
    {
        return $this->format() < (new static('today'))->format();
    }

}
