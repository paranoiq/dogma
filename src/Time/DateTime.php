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
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Format\DateTimeFormatter;
use Dogma\Time\Format\DateTimeValues;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Type;

/**
 * Immutable date and time class.
 *
 * Timestamps are always considered to be based on UTC.
 *
 * Comparisons and intervals are based on microseconds since unix epoch, giving a possible range of about ±280.000 years.
 */
class DateTime extends \DateTimeImmutable implements DateOrTime, \DateTimeInterface
{
    use StrictBehaviorMixin;
    use NonIterableMixin;

    public const MIN = '0001-01-01 00:00:00.000000';
    public const MAX = '9999-12-31 23:59:59.999999';

    public const MIN_MICRO_TIMESTAMP = -62135596800000000;
    public const MAX_MICRO_TIMESTAMP = 253402300799999999;

    public const DEFAULT_FORMAT = 'Y-m-d H:i:s.u';
    public const FORMAT_EMAIL_HTTP = DATE_RFC2822;

    /** @var int */
    private $microTimestamp;

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $format
     * @param string $timeString
     * @param \DateTimeZone|null $timeZone
     * @return \Dogma\Time\DateTime
     */
    public static function createFromFormat($format, $timeString, $timeZone = null): self
    {
        // due to invalid typehint in parent class...
        Check::nullableObject($timeZone, \DateTimeZone::class);

        // due to invalid optional arguments handling...
        if ($timeZone === null) {
            $dateTime = parent::createFromFormat($format, $timeString);
        } else {
            $dateTime = parent::createFromFormat($format, $timeString, $timeZone);
        }
        if ($dateTime === false) {
            throw new InvalidDateTimeException($timeString);
        }

        return new static($dateTime->format(self::DEFAULT_FORMAT), $timeZone ?? $dateTime->getTimezone());
    }

    public static function createFromTimestamp(int $timestamp, ?\DateTimeZone $timeZone = null): self
    {
        $dateTime = static::createFromFormat('U', (string) $timestamp, TimeZone::getUtc());
        if ($timeZone === null) {
            $timeZone = TimeZone::getDefault();
        }
        $dateTime = $dateTime->setTimezone($timeZone);

        return $dateTime;
    }

    public static function createFromFloatTimestamp(float $timestamp, ?\DateTimeZone $timeZone = null): self
    {
        $formatted = number_format($timestamp, 6, '.', '');

        $dateTime = static::createFromFormat('U.u', $formatted, TimeZone::getUtc());
        if ($timeZone === null) {
            $timeZone = TimeZone::getDefault();
        }
        $dateTime = $dateTime->setTimezone($timeZone);

        return $dateTime;
    }

    public static function createFromMicroTimestamp(int $microTimestamp, ?\DateTimeZone $timeZone = null): self
    {
        $timestamp = (int) floor($microTimestamp / 1000000);
        $microseconds = $microTimestamp - $timestamp * 1000000;

        $dateTime = static::createFromTimestamp($timestamp, TimeZone::getUtc())->modify('+' . $microseconds . ' microseconds');
        if ($timeZone === null) {
            $timeZone = TimeZone::getDefault();
        }
        $dateTime = $dateTime->setTimezone($timeZone);

        return $dateTime;
    }

    public static function createFromDateTimeInterface(\DateTimeInterface $dateTime, ?\DateTimeZone $timeZone = null): self
    {
        if ($timeZone === null) {
            $timeZone = $dateTime->getTimezone();
        }
        $timestamp = $dateTime->getTimestamp();
        $microseconds = (int) $dateTime->format('u');

        return self::createFromTimestamp($timestamp, $timeZone)->modify('+' . $microseconds . ' microseconds');
    }

    public static function createFromDateAndTime(Date $date, Time $time, ?\DateTimeZone $timeZone = null): self
    {
        return new static($date->format(Date::DEFAULT_FORMAT) . ' ' . $time->format(Time::DEFAULT_FORMAT), $timeZone);
    }

    /**
     * Called by modify() etc.
     */
    public function __clone()
    {
        $this->microTimestamp = null;
    }

    // modifications ---------------------------------------------------------------------------------------------------

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \DateInterval $interval
     * @return self
     */
    public function add($interval): self
    {
        $that = parent::add($interval);

        return static::createFromDateTimeInterface($that);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \DateInterval $interval
     * @return self
     */
    public function sub($interval): self
    {
        $that = parent::sub($interval);

        return static::createFromDateTimeInterface($that);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Time\Time|int|string $time
     * @param int|null $minutes
     * @param int|null $seconds
     * @param int|null $microseconds
     * @return self
     */
    public function setTime($time, $minutes = null, $seconds = null, $microseconds = null): self
    {
        if ($time instanceof Time) {
            return self::createFromDateTimeInterface(parent::setTime($time->getHours(), $time->getMinutes(), $time->getSeconds(), $time->getMicroseconds()));
        }
        if ($minutes === null && $seconds === null && is_string($time) && Str::contains($time, ':')) {
            $parts = explode(':', $time);
            $time = $parts[0];
            $minutes = $parts[1] ?? null;
            $seconds = (string) $parts[2] ?? '';
            if (Str::contains($seconds, '.')) {
                [$seconds, $microseconds] = explode('.', $seconds);
                $microseconds = (int) (('0.' . $microseconds) * 1000000);
            }
        }

        return self::createFromDateTimeInterface(parent::setTime((int) $time, (int) $minutes, (int) $seconds, (int) $microseconds));
    }

    // queries ---------------------------------------------------------------------------------------------------------

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $format
     * @param \Dogma\Time\Format\DateTimeFormatter|null $formatter
     * @return string
     */
    public function format($format = self::DEFAULT_FORMAT, ?DateTimeFormatter $formatter = null): string
    {
        if ($formatter === null) {
            return parent::format($format);
        } else {
            return $formatter->format($this, $format);
        }
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        $other instanceof self || Check::object($other, self::class);

        return $this > $other ? 1 : ($other > $this ? -1 : 0);
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->getMicroTimestamp() === $other->getMicroTimestamp();
    }

    public function equalsUpTo(\DateTimeInterface $other, DateTimeUnit $unit): bool
    {
        $format = $unit->getComparisonFormat();

        return $this->format($format) === $other->format($format);
    }

    public function timeZoneEquals(\DateTimeInterface $other): bool
    {
        return $this->getTimezone()->getName() === $other->getTimezone()->getName();
    }

    public function timeOffsetEquals(\DateTimeInterface $other): bool
    {
        return $this->getTimezone()->getOffset($this) === $other->getTimezone()->getOffset($other);
    }

    public function isBefore(\DateTimeInterface $dateTime): bool
    {
        return $this < $dateTime;
    }

    public function isAfter(\DateTimeInterface $dateTime): bool
    {
        return $this > $dateTime;
    }

    public function isBetween(\DateTimeInterface $sinceTime, \DateTimeInterface $untilTime): bool
    {
        return $this >= $sinceTime && $this <= $untilTime;
    }

    public function isFuture(?TimeProvider $timeProvider = null): bool
    {
        return $this > ($timeProvider !== null ? $timeProvider->getDateTime() : new self());
    }

    public function isPast(?TimeProvider $timeProvider = null): bool
    {
        return $this < ($timeProvider !== null ? $timeProvider->getDateTime() : new self());
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return bool
     */
    public function isSameDay($date): bool
    {
        Check::types($date, [\DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) === $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return bool
     */
    public function isBeforeDay($date): bool
    {
        Check::types($date, [\DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) < $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @return bool
     */
    public function isAfterDay($date): bool
    {
        Check::types($date, [\DateTimeInterface::class, Date::class]);

        return $this->format(Date::DEFAULT_FORMAT) > $date->format(Date::DEFAULT_FORMAT);
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $sinceDate
     * @param \DateTimeInterface|\Dogma\Time\Date $untilDate
     * @return bool
     */
    public function isBetweenDays($sinceDate, $untilDate): bool
    {
        Check::types($sinceDate, [\DateTimeInterface::class, Date::class]);
        Check::types($untilDate, [\DateTimeInterface::class, Date::class]);

        $thisDate = $this->format(Date::DEFAULT_FORMAT);

        return $thisDate >= $sinceDate->format(Date::DEFAULT_FORMAT)
            && $thisDate <= $untilDate->format(Date::DEFAULT_FORMAT);
    }

    public function isToday(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date('today');

        return $this->isBetween($today->getStart(), $today->getEnd());
    }

    public function isYesterday(?TimeProvider $timeProvider = null): bool
    {
        $yesterday = $timeProvider !== null ? $timeProvider->getDateTime()->modify('-1 day')->getDate() : new Date('yesterday');

        return $this->isBetween($yesterday->getStart(), $yesterday->getEnd());
    }

    public function isTomorrow(?TimeProvider $timeProvider = null): bool
    {
        $tomorrow = $timeProvider !== null ? $timeProvider->getDateTime()->modify('+1 day')->getDate() : new Date('tomorrow');

        return $this->isBetween($tomorrow->getStart(), $tomorrow->getEnd());
    }

    /**
     * @param int|\Dogma\Time\DayOfWeek $day
     * @return bool
     */
    public function isDayOfWeek($day): bool
    {
        Check::types($day, [Type::INT, DayOfWeek::class]);

        if (is_int($day)) {
            $day = DayOfWeek::get($day);
        }

        return (int) $this->format('N') === $day->getValue();
    }

    public function isWeekend(): bool
    {
        return $this->format('N') > 5;
    }

    /**
     * @param int|\Dogma\Time\Month $month
     * @return bool
     */
    public function isMonth($month): bool
    {
        Check::types($month, [Type::INT, Month::class]);

        if (is_int($month)) {
            $month = Month::get($month);
        }

        return (int) $this->format('n') === $month->getValue();
    }

    // getters ---------------------------------------------------------------------------------------------------------

    public function getDate(): Date
    {
        return new Date($this->format(Date::DEFAULT_FORMAT));
    }

    public function getTime(): Time
    {
        return new Time($this->format(Time::DEFAULT_FORMAT));
    }

    public function getMicroTimestamp(): int
    {
        if ($this->microTimestamp === null) {
            $timestamp = $this->getTimestamp();
            $microseconds = (int) $this->format('u');
            $this->microTimestamp = $timestamp * 1000000 + $microseconds;
        }

        return $this->microTimestamp;
    }

    public function getYear(): int
    {
        return (int) $this->format('Y');
    }

    public function getMonth(): int
    {
        return (int) $this->format('m');
    }

    public function getMonthEnum(): Month
    {
        return Month::get((int) $this->format('n'));
    }

    public function getDay(): int
    {
        return (int) $this->format('d');
    }

    public function getDayOfWeek(): int
    {
        return (int) $this->format('N');
    }

    public function getDayOfWeekEnum(): DayOfWeek
    {
        return DayOfWeek::get((int) $this->format('N'));
    }

    public function fillValues(DateTimeValues $values): void
    {
        $results = explode('|', $this->format('Y|L|z|m|d|N|W|o|H|i|s|v|u|p|P'));

        $values->year = (int) $results[0];
        $values->leapYear = (bool) $results[1];
        $values->dayOfYear = (int) $results[2];
        $values->quarter = (int) ($results[3] / 3);
        $values->month = (int) $results[3];
        $values->day = (int) $results[4];
        $values->dayOfWeek = (int) $results[5];
        $values->weekOfYear = (int) $results[6];
        $values->isoWeekYear = (int) $results[7];

        $values->hours = (int) $results[8];
        $values->minutes = (int) $results[9];
        $values->seconds = (int) $results[10];
        $values->miliseconds = (int) $results[11];
        $values->microseconds = (int) $results[12];

        $values->dst = (bool) $results[13];
        $values->offset = $results[14];
        $values->timezone = $this->getTimezone();
    }

}
