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

    /** @var int */
    private $secondsSinceMidnight;

    /**
     * @param string|int $time
     */
    public function __construct($time)
    {
        if (is_numeric($time)) {
            Check::int($time, 0, self::SECONDS_IN_A_DAY);
            $this->secondsSinceMidnight = $time;
        } else {
            try {
                $dateTime = new \DateTime($time);
            } catch (\Throwable $e) {
                throw new \Dogma\Time\InvalidDateTimeException($e);
            }
            $hours = (int) $dateTime->format('h');
            $minutes = (int) $dateTime->format('i');
            $seconds = (int) $dateTime->format('s');
            $this->secondsSinceMidnight = $hours * 3600 + $minutes * 60 + $seconds;
        }
    }

    public static function createFromParts(int $hours, int $minutes, int $seconds = 0): self
    {
        Check::int($hours, 0, 23);
        Check::int($minutes, 0, 59);
        Check::int($seconds, 0, 59);

        return new static($hours * 3600 + $minutes * 60 + $seconds);
    }

    public static function createFromSeconds(int $secondsSinceMidnight): self
    {
        return new static($secondsSinceMidnight);
    }

    public static function createFromFormat(string $format, string $timeString): self
    {
        Check::string($format);
        Check::string($timeString);

        $dateTime = \DateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new \Dogma\Time\InvalidDateTimeException('xxx');
        }

        return new static($dateTime->format(self::DEFAULT_FORMAT));
    }

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        $midnightTimestamp = mktime(0, 0, 0);
        return date($format, $midnightTimestamp + $this->secondsSinceMidnight);
    }

    public function getSecondsSinceMidnight(): int
    {
        return $this->secondsSinceMidnight;
    }

    public function getHours(): int
    {
        return (int) floor($this->secondsSinceMidnight / 3600);
    }

    public function getMinutes(): int
    {
        return floor($this->secondsSinceMidnight / 60) % 60;
    }

    public function getSeconds(): int
    {
        return $this->secondsSinceMidnight % 60;
    }

    /**
     * @param \Dogma\Time\Time|string|int $time
     * @return bool
     */
    public function isEqual($time): bool
    {
        if (!$time instanceof Time) {
            $time = new static($time);
        }
        return $this->getSecondsSinceMidnight() === $time->getSecondsSinceMidnight();
    }

    /**
     * @param \Dogma\Time\Time|string|int $since
     * @param \Dogma\Time\Time|string|int $until
     * @return bool
     */
    public function isBetween($since, $until): bool
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
