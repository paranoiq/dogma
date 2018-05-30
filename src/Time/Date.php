<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\NonIterableMixin;
use Dogma\Order;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Type;

/**
 * Date class.
 */
class Date implements DateOrTime
{
    use StrictBehaviorMixin;
    use NonIterableMixin;

    public const MIN = '0001-01-01';
    public const MAX = '9999-12-31';

    public const MIN_DAY_NUMBER = 0;
    public const MAX_DAY_NUMBER = 3652058;

    public const DEFAULT_FORMAT = 'Y-m-d';

    /** @var int */
    private $dayNumber;

    /** @var \DateTimeImmutable|null */
    private $dateTime;

    /**
     * @param int|string $dayNumberOrDateString
     */
    public function __construct($dayNumberOrDateString = 'today')
    {
        if (is_int($dayNumberOrDateString)) {
            Check::range($dayNumberOrDateString, self::MIN_DAY_NUMBER, self::MAX_DAY_NUMBER);
            $this->dayNumber = $dayNumberOrDateString;
        } else {
            try {
                $this->dateTime = (new \DateTimeImmutable($dayNumberOrDateString))->setTime(0, 0, 0);
                $this->dayNumber = self::calculateDayNumber($this->dateTime);
            } catch (\Throwable $e) {
                throw new InvalidDateTimeException($dayNumberOrDateString, $e);
            }
        }
    }

    public static function createFromTimestamp(int $timestamp): Date
    {
        return DateTime::createFromTimestamp($timestamp)->getDate();
    }

    public static function createFromDateTimeInterface(\DateTimeInterface $dateTime): Date
    {
        if ($dateTime instanceof DateTime) {
            return $dateTime->getDate();
        } else {
            return DateTime::createFromDateTimeInterface($dateTime)->getDate();
        }
    }

    public static function createFromComponents(int $year, int $month, int $day): self
    {
        Check::range($year, 1, 9999);
        Check::range($month, 1, 12);
        Check::range($day, 1, 31);

        return new static(sprintf('%d-%d-%d 00:00:00', $year, $month, $day));
    }

    public static function createFromDayNumber(int $dayNumber): self
    {
        return new static($dayNumber);
    }

    public static function createFromFormat(string $format, string $timeString): self
    {
        $dateTime = \DateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new InvalidDateTimeException('xxx');
        }

        return self::createFromDateTimeInterface($dateTime);
    }

    final public function __clone()
    {
        $this->dateTime = null;
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function modify(string $value): self
    {
        return static::createFromDateTimeInterface($this->getDateTime()->modify($value));
    }

    public function addDay(): self
    {
        return new static($this->dayNumber + 1);
    }

    public function subtractDay(): self
    {
        return new static($this->dayNumber - 1);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return $this->getDateTime()->format($format);
    }

    public function toDateTime(?\DateTimeZone $timeZone = null): DateTime
    {
        return DateTime::createFromDateAndTime($this, new Time(0), $timeZone);
    }

    public function getDayNumber(): int
    {
        return $this->dayNumber;
    }

    /**
     * Returns number of day since 0001-01-01 (day 0)
     * @param \DateTimeInterface $dateTime
     * @return int
     */
    public static function calculateDayNumber(\DateTimeInterface $dateTime): int
    {
        $start = new \DateTimeImmutable(self::MIN . ' 00:00:00');
        $diff = $dateTime->diff($start, true);

        return $diff->days;
    }

    /**
     * @param \DateTimeInterface|\Dogma\Time\Date $date
     * @param bool $absolute
     * @return \DateInterval
     */
    public function diff($date, bool $absolute = false): \DateInterval
    {
        Check::types($date, [\DateTimeInterface::class, self::class]);

        return (new \DateTimeImmutable($this->format()))->diff(new \DateTimeImmutable($date->format(self::DEFAULT_FORMAT)), $absolute);
    }

    public function getStart(?\DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(0, 0, 0);
    }

    public function getStartFormatted(?string $format = null, ?\DateTimeZone $timeZone = null): string
    {
        return $this->getStart($timeZone)->format($format ?? DateTime::DEFAULT_FORMAT);
    }

    public function getEnd(?\DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(23, 59, 59);
    }

    public function getEndFormatted(?string $format = null, ?\DateTimeZone $timeZone = null): string
    {
        return $this->getStart($timeZone)->setTime(23, 59, 59)->format($format ?? DateTime::DEFAULT_FORMAT);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->dayNumber <=> $other->dayNumber;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->dayNumber === $other->dayNumber;
    }

    public function isBefore(Date $date): bool
    {
        return $this->dayNumber < $date->dayNumber;
    }

    public function isAfter(Date $date): bool
    {
        return $this->dayNumber > $date->dayNumber;
    }

    public function isSameOrBefore(Date $date): bool
    {
        return $this->dayNumber <= $date->dayNumber;
    }

    public function isSameOrAfter(Date $date): bool
    {
        return $this->dayNumber >= $date->dayNumber;
    }

    public function isBetween(Date $sinceDate, Date $untilDate): bool
    {
        return $this->dayNumber >= $sinceDate->dayNumber && $this->dayNumber <= $untilDate->dayNumber;
    }

    public function isFuture(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date();

        return $this->dayNumber > $today->dayNumber;
    }

    public function isPast(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date();

        return $this->dayNumber < $today->dayNumber;
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

        return (($this->dayNumber % 7) + 1) === $day->getValue();
    }

    public function isWeekend(): bool
    {
        return (($this->dayNumber % 7) + 1) > DayOfWeek::FRIDAY;
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

    public function getDayOfWeekEnum(): DayOfWeek
    {
        return DayOfWeek::get(($this->dayNumber % 7) + 1);
    }

    public function getMonthEnum(): Month
    {
        return Month::get((int) $this->format('n'));
    }

    private function getDateTime(): \DateTimeImmutable
    {
        if ($this->dateTime === null) {
            $this->dateTime = new \DateTimeImmutable(self::MIN . ' +' . $this->dayNumber . ' days');
        }

        return $this->dateTime;
    }

    // static ----------------------------------------------------------------------------------------------------------

    public static function min(self ...$items): self
    {
        return Arr::minBy($items, function (self $date) {
            return $date->dayNumber;
        });
    }

    public static function max(self ...$items): self
    {
        return Arr::maxBy($items, function (self $date) {
            return $date->dayNumber;
        });
    }

    /**
     * @param \Dogma\Time\Date[] $items
     * @param int $flags
     * @return \Dogma\Time\Date[]
     */
    public static function sort(array $items, int $flags = Order::ASCENDING): array
    {
        return Arr::sortWith($items, function (Date $a, Date $b) {
            return $a->dayNumber <=> $b->dayNumber;
        }, $flags);
    }

}
