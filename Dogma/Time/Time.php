<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Check;

/**
 * Time of day.
 */
class Time implements \Dogma\NonIterable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;

    const DEFAULT_FORMAT = 'H:i:s';

    const SECONDS_IN_A_DAY = 86400;

    /** @var integer */
    private $secondsSinceMidnight;

    /**
     * @param string|integer $time
     */
    public function __construct($time)
    {
        if (is_numeric($time)) {
            Check::integer($time, 0, self::SECONDS_IN_A_DAY);
            $this->secondsSinceMidnight = $time;
        } else {
            try {
                $dateTime = new \DateTime($time);
            } catch (\Exception $e) {
                throw new \Dogma\Time\InvalidDateTimeException($e);
            }
            $hours = (int) $dateTime->format('h');
            $minutes = (int) $dateTime->format('i');
            $seconds = (int) $dateTime->format('s');
            $this->secondsSinceMidnight = $hours * 3600 + $minutes * 60 + $seconds;
        }
    }

    /**
     * @param integer $hours
     * @param integer $minutes
     * @param integer $seconds
     * @return static
     */
    public static function createFromParts($hours, $minutes, $seconds = 0)
    {
        Check::integer($hours, 0, 23);
        Check::integer($minutes, 0, 59);
        Check::integer($seconds, 0, 59);

        return new static($hours * 3600 + $minutes * 60 + $seconds);
    }

    /**
     * @param integer $secondsSinceMidnight
     * @return static
     */
    public static function createFromSeconds($secondsSinceMidnight)
    {
        return new static($secondsSinceMidnight);
    }

    /**
     * @param string $format
     * @param string $timeString
     * @return static
     */
    public static function createFromFormat($format, $timeString)
    {
        Check::string($format);
        Check::string($timeString);

        $dateTime = \DateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new \Dogma\Time\InvalidDateTimeException('xxx');
        }

        return new static($dateTime->format(self::DEFAULT_FORMAT));
    }

    /**
     * @param string $format
     * @return string
     */
    public function format($format = self::DEFAULT_FORMAT)
    {
        $midnightTimestamp = mktime(0, 0, 0);
        return date($format, $midnightTimestamp + $this->secondsSinceMidnight);
    }

    /**
     * @return integer
     */
    public function getSecondsSinceMidnight()
    {
        return $this->secondsSinceMidnight;
    }

    /**
     * @return integer
     */
    public function getHours()
    {
        return (int) floor($this->secondsSinceMidnight / 3600);
    }

    /**
     * @return integer
     */
    public function getMinutes()
    {
        return floor($this->secondsSinceMidnight / 60) % 60;
    }

    /**
     * @return integer
     */
    public function getSeconds()
    {
        return $this->secondsSinceMidnight % 60;
    }

    /**
     * @param \Dogma\Time\Time|string|integer $time
     * @return boolean
     */
    public function isEqual($time)
    {
        if (!$time instanceof Time) {
            $time = new static($time);
        }
        return $this->getSecondsSinceMidnight() === $time->getSecondsSinceMidnight();
    }

    /**
     * @param \Dogma\Time\Time|string|integer $since
     * @param \Dogma\Time\Time|string|integer $until
     * @return boolean
     */
    public function isBetween($since, $until)
    {
        if (!$since instanceof Time) {
            $since = new static($since);
        }
        if (!$until instanceof Time) {
            $until = new static($until);
        }
        $sinceSeconds = $since->getSecondsSinceMidnight();
        $untilSeconds = $until->getSecondsSinceMidnight();
        $thisSeconds = $this->getSecondsSinceMidnight();

        if ($sinceSeconds < $untilSeconds) {
            return $thisSeconds >= $sinceSeconds && $thisSeconds <= $untilSeconds;
        } elseif ($sinceSeconds > $untilSeconds) {
            return $thisSeconds >= $sinceSeconds || $thisSeconds <= $untilSeconds;
        } else {
            return $thisSeconds === $sinceSeconds;
        }
    }

}
