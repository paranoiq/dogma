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
use Dogma\Order;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Format\DateTimeFormatter;
use Dogma\Time\Format\DateTimeValues;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Time\Span\DateSpan;
use Dogma\Type;
use function explode;
use function gregoriantojd;
use function intval;
use function is_int;
use function jdtogregorian;
use function sprintf;

/**
 * Date class.
 */
class Date implements DateOrDateTime, Pokeable
{
    use StrictBehaviorMixin;

    public const MIN = '0001-01-01';
    public const MAX = '9999-12-31';

    public const MIN_DAY_NUMBER = 1721426;
    public const MAX_DAY_NUMBER = 5373484;

    public const DEFAULT_FORMAT = 'Y-m-d';

    /** @var int */
    private $julianDay;

    /** @var \DateTimeImmutable|null */
    private $dateTime;

    /**
     * @param int|string $julianDayOrDateString
     */
    public function __construct($julianDayOrDateString = 'today')
    {
        if (is_int($julianDayOrDateString)) {
            Check::range($julianDayOrDateString, self::MIN_DAY_NUMBER, self::MAX_DAY_NUMBER);
            $this->julianDay = $julianDayOrDateString;
        } else {
            try {
                $this->dateTime = (new \DateTimeImmutable($julianDayOrDateString))->setTime(0, 0, 0);
                $this->julianDay = self::calculateDayNumber($this->dateTime);
            } catch (\Throwable $e) {
                throw new InvalidDateTimeException($julianDayOrDateString, $e);
            }
        }
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

    public static function createFromIsoYearAndWeek(int $year, int $week, int $dayOfWeek): self
    {
        Check::range($year, 1, 9999);
        Check::range($week, 1, 53);
        Check::range($dayOfWeek, 1, 7);

        $dateTime = new \DateTime('today 00:00:00');
        $dateTime->setISODate($year, $week, $dayOfWeek);

        return static::createFromDateTimeInterface($dateTime);
    }

    public static function createFromJulianDay(int $julianDay): self
    {
        return new static($julianDay);
    }

    public static function createFromFormat(string $format, string $timeString): self
    {
        $dateTime = \DateTime::createFromFormat($format, $timeString);
        if ($dateTime === false) {
            throw new InvalidDateTimeException('xxx');
        }

        return self::createFromDateTimeInterface($dateTime);
    }

    public function poke(): void
    {
        $this->getDateTime();
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
        return new static($this->julianDay + 1);
    }

    public function addDays(int $days): self
    {
        return new static($this->julianDay + $days);
    }

    public function subtractDay(): self
    {
        return new static($this->julianDay - 1);
    }

    public function subtractDays(int $days): self
    {
        return new static($this->julianDay - $days);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeFormatter $formatter = null): string
    {
        if ($formatter === null) {
            return $this->getDateTime()->format($format);
        } else {
            return $formatter->format($this, $format);
        }
    }

    public function toDateTime(?\DateTimeZone $timeZone = null): DateTime
    {
        return DateTime::createFromDateAndTime($this, new Time(0), $timeZone);
    }

    public function toDateTimeInterval(?\DateTimeZone $timeZone = null): DateTimeInterval
    {
        return new DateTimeInterval($this->getStart($timeZone), $this->addDay()->getStart(), false, true);
    }

    public function getJulianDay(): int
    {
        return $this->julianDay;
    }

    /**
     * Returns Julian day number (count of days since January 1st, 4713 B.C.)
     * @param \DateTimeInterface $dateTime
     * @return int
     */
    public static function calculateDayNumber(\DateTimeInterface $dateTime): int
    {
        [$y, $m, $d] = explode('-', $dateTime->format(self::DEFAULT_FORMAT));

        return gregoriantojd(intval($m), intval($d), intval($y));
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

    public function difference(Date $other, bool $absolute = false): DateSpan
    {
        $interval = self::diff($other, $absolute);

        return DateSpan::createFromDateInterval($interval);
    }

    public function getStart(?\DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(0, 0, 0);
    }

    public function getEnd(?\DateTimeZone $timeZone = null): DateTime
    {
        return (new DateTime($this->format(), $timeZone))->setTime(23, 59, 59, 999999);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->julianDay <=> $other->julianDay;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->julianDay === $other->julianDay;
    }

    public function isBefore(Date $date): bool
    {
        return $this->julianDay < $date->julianDay;
    }

    public function isAfter(Date $date): bool
    {
        return $this->julianDay > $date->julianDay;
    }

    public function isSameOrBefore(Date $date): bool
    {
        return $this->julianDay <= $date->julianDay;
    }

    public function isSameOrAfter(Date $date): bool
    {
        return $this->julianDay >= $date->julianDay;
    }

    public function isBetween(Date $sinceDate, Date $untilDate): bool
    {
        return $this->julianDay >= $sinceDate->julianDay && $this->julianDay <= $untilDate->julianDay;
    }

    public function isFuture(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date();

        return $this->julianDay > $today->julianDay;
    }

    public function isPast(?TimeProvider $timeProvider = null): bool
    {
        $today = $timeProvider !== null ? $timeProvider->getDate() : new Date();

        return $this->julianDay < $today->julianDay;
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

        return (($this->julianDay % 7) + 1) === $day->getValue();
    }

    public function isWeekend(): bool
    {
        return (($this->julianDay % 7) + 1) > DayOfWeek::FRIDAY;
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

    private function getDateTime(): \DateTimeImmutable
    {
        if ($this->dateTime === null) {
            [$m, $d, $y] = explode('/', jdtogregorian($this->julianDay));

            $this->dateTime = new \DateTimeImmutable($y . '-' . $m . '-' . $d . ' 00:00:00');
        }

        return $this->dateTime;
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
        return ($this->julianDay % 7) + 1;
    }

    public function getDayOfWeekEnum(): DayOfWeek
    {
        return DayOfWeek::get(($this->julianDay % 7) + 1);
    }

    public function fillValues(DateTimeValues $values): void
    {
        $results = explode('|', $this->format('Y|L|z|m|d|N|W|o'));

        $values->year = (int) $results[0];
        $values->leapYear = (bool) $results[1];
        $values->dayOfYear = (int) $results[2];
        $values->quarter = (int) ($results[3] / 3);
        $values->month = (int) $results[3];
        $values->day = (int) $results[4];
        $values->dayOfWeek = (int) $results[5];
        $values->weekOfYear = (int) $results[6];
        $values->isoWeekYear = (int) $results[7];
    }

    // static ----------------------------------------------------------------------------------------------------------

    public static function min(self ...$items): self
    {
        return Arr::minBy($items, function (self $date) {
            return $date->julianDay;
        });
    }

    public static function max(self ...$items): self
    {
        return Arr::maxBy($items, function (self $date) {
            return $date->julianDay;
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
            return $a->julianDay <=> $b->julianDay;
        }, $flags);
    }

}
