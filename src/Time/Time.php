<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\NonIterableMixin;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Format\DateTimeFormatter;
use Dogma\Time\Format\DateTimeValues;

/**
 * Time of day.
 */
class Time implements DateOrTime
{
    use StrictBehaviorMixin;
    use NonIterableMixin;

    public const MIN = '00:00:00.000000';
    public const MAX = '23:59:59.999999';

    public const DAY_MICROSECONDS = Seconds::DAY * 1000000;

    public const MIN_MICROSECONDS = 0;
    public const MAX_MICROSECONDS = self::DAY_MICROSECONDS - 1;

    public const DEFAULT_FORMAT = 'H:i:s';

    /** @var int|string */
    private $microseconds;

    /** @var \DateTimeImmutable|null */
    private $dateTime;

    /**
     * @param int|string $microsecondsOrTimeString
     */
    public function __construct($microsecondsOrTimeString)
    {
        if (is_int($microsecondsOrTimeString)) {
            Check::range($microsecondsOrTimeString, self::MIN_MICROSECONDS, self::MAX_MICROSECONDS);

            $this->microseconds = $microsecondsOrTimeString;
        } else {
            try {
                $dateTime = new \DateTime($microsecondsOrTimeString);
            } catch (\Throwable $e) {
                throw new InvalidDateTimeException($microsecondsOrTimeString, $e);
            }

            $hours = (int) $dateTime->format('H');
            $minutes = (int) $dateTime->format('i');
            $seconds = (int) $dateTime->format('s');
            $microseconds = (int) $dateTime->format('u');

            Check::range($hours, 0, 23);
            Check::range($minutes, 0, 59);
            Check::range($seconds, 0, 59);
            Check::range($microseconds, 0, 1000000);

            $this->microseconds = ($hours * 3600 + $minutes * 60 + $seconds) * 1000000 + $microseconds;
        }
    }

    public static function createFromSeconds(int $secondsSinceMidnight): self
    {
        return new static($secondsSinceMidnight * 1000000);
    }

    public static function createFromParts(int $hours, int $minutes = 0, int $seconds = 0, int $microseconds = 0): self
    {
        Check::range($hours, 0, 23);
        Check::range($minutes, 0, 59);
        Check::range($seconds, 0, 59);
        Check::range($microseconds, 0, 1000000);

        return new static(($hours * 3600 + $minutes * 60 + $seconds) * 1000000 + $microseconds);
    }

    public static function createFromFormat(string $format, string $timeString): self
    {
        $dateTime = \DateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new InvalidDateTimeException('xxx');
        }

        $hours = (int) $dateTime->format('h');
        $minutes = (int) $dateTime->format('i');
        $seconds = (int) $dateTime->format('s');
        $microseconds = (int) $dateTime->format('u');

        return self::createFromParts($hours, $minutes, $seconds, $microseconds);
    }

    final public function __clone()
    {
        $this->dateTime = null;
    }

    public function toDateTime(?Date $date = null, ?\DateTimeZone $timeZone = null): DateTime
    {
        return DateTime::createFromDateAndTime($date ?? new Date(), $this, $timeZone);
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeFormatter $formatter = null): string
    {
        if ($formatter === null) {
            return $this->getDateTime()->format($format);
        } else {
            return $formatter->format($this, $format);
        }
    }

    private function getDateTime(): \DateTimeImmutable
    {
        if ($this->dateTime === null) {
            $total = $this->microseconds % self::DAY_MICROSECONDS;
            $seconds = (int) floor($total / 1000000);
            $microseconds = $total - ($seconds * 1000000);
            $this->dateTime = new \DateTimeImmutable(DateTime::MIN . ' +' . $seconds . ' seconds +' . $microseconds . ' microseconds');
        }

        return $this->dateTime;
    }

    public function getMicroTime(): int
    {
        return $this->microseconds;
    }

    public function getHours(): int
    {
        return (int) floor($this->microseconds / 1000000 / 3600);
    }

    public function getMinutes(): int
    {
        return floor($this->microseconds / 1000000 / 60) % 60;
    }

    public function getSeconds(): int
    {
        return floor($this->microseconds / 1000000) % 60;
    }

    public function getMicroseconds(): int
    {
        return $this->microseconds % 1000000;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->microseconds === $other->microseconds;
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->microseconds <=> $other->microseconds;
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
        $sinceSeconds = $since->microseconds;
        $untilSeconds = $until->microseconds;
        $thisSeconds = $this->microseconds;

        if ($sinceSeconds < $untilSeconds) {
            return $thisSeconds >= $sinceSeconds && $thisSeconds <= $untilSeconds;
        } elseif ($sinceSeconds > $untilSeconds) {
            return $thisSeconds >= $sinceSeconds || $thisSeconds <= $untilSeconds;
        } else {
            return $thisSeconds === $sinceSeconds;
        }
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Time $time
     * @param bool $absolute
     * @return \DateInterval
     */
    public function diff($time, bool $absolute = false): \DateInterval
    {
        Check::types($time, [\DateTimeInterface::class, self::class]);

        return (new \DateTimeImmutable($this->format()))->diff(new \DateTimeImmutable($time->format(self::DEFAULT_FORMAT)), $absolute);
    }

    public function fillValues(DateTimeValues $values): void
    {
        $results = explode('|', $this->format('H|i|s|v|u'));

        $values->hours = (int) $results[0];
        $values->minutes = (int) $results[1];
        $values->seconds = (int) $results[2];
        $values->miliseconds = (int) $results[3];
        $values->microseconds = (int) $results[4];
    }

}
