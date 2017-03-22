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
 * Immutable date and time class
 */
class DateTime extends \DateTimeImmutable implements \Dogma\NonIterable, \DateTimeInterface
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;
    use \Dogma\Time\DateDateTimeCommonMixin;

    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';
    public const FORMAT_EMAIL_HTTP = DATE_RFC2822;

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

        return new static($dateTime->format(self::DEFAULT_FORMAT), $timeZone ?? $dateTime->getTimezone());
    }

    public static function createFromTimestamp(int $timestamp, ?\DateTimeZone $timeZone = null): self
    {
        return static::createFromFormat('U', (string) $timestamp, $timeZone);
    }

    public static function createFromDateTimeInterface(\DateTimeInterface $dateTime, ?\DateTimeZone $timeZone = null): self
    {
        if ($timeZone === null) {
            $timeZone = $dateTime->getTimezone();
        }
        return new static($dateTime->format(self::DEFAULT_FORMAT), $timeZone);
    }

    public static function createFromDateAndTime(Date $date, Time $time, ?\DateTimeZone $timeZone = null): self
    {
        return new static($date->format(Date::DEFAULT_FORMAT) . ' ' . $time->format(Time::DEFAULT_FORMAT), $timeZone);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $modify
     * @return static
     */
    public function modify($modify): self
    {
        return new static(parent::modify($modify));
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Time\Time|int $time
     * @param int|null $minutes
     * @param int|null $seconds
     * @param int|null $microseconds
     * @return self
     */
    public function setTime($time, $minutes = null, $seconds = null, $microseconds = null): self
    {
        if ($time instanceof Time) {
            return self::createFromDateTimeInterface(parent::setTime($time->getHours(), $time->getMinutes(), $time->getSeconds()));
        }
        if (is_string($time) && $minutes === null && $seconds === null) {
            list($time, $minutes, $seconds) = explode(':', $time);
        }

        return self::createFromDateTimeInterface(parent::setTime($time, $minutes, $seconds, $microseconds));
    }

    public function compare(\DateTimeInterface $dateTime): int
    {
        return $this > $dateTime ? 1 : ($dateTime > $this ? -1 : 0);
    }

    public function isEqual(\DateTimeInterface $dateTime): bool
    {
        return $this->getTimestamp() === $dateTime->getTimestamp();
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

}
