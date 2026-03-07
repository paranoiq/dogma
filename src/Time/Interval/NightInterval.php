<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use DateTimeInterface;
use DateTimeZone;
use Dogma\Arr;
use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\IntersectComparable;
use Dogma\Math\Interval\Interval;
use Dogma\Math\Interval\IntervalCalc;
use Dogma\Math\Interval\IntervalDumpMixin;
use Dogma\Math\Interval\IntervalParser;
use Dogma\Math\Interval\IntInterval;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Time;
use Dogma\Time\ValueOutOfAllowedRangeException;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function round;

/**
 * Interval of nights (e.g. length of stay in a hotel in days). Based on IntInterval.
 *
 * Is calculated as if it was an interval with open end. The end date is not considered as included.
 * Appears to be one day "longer" than relevant DateInterval, in fact it is basically shifted forward by undefined number of hours.
 *
 * This class exist to emphasize the difference between "list of days" (DateInterval) and "time to stay" (NightInterval) types
 * while keeping the internals human friendly and thus preventing off-by-one errors.
 *
 * @implements Interval<Date, NightIntervalSet>
 */
class NightInterval implements Interval, DateOrTimeInterval, Pokeable
{
    use IntervalDumpMixin;
    use StrictBehaviorMixin;

    public const MIN = Date::MIN;
    public const MAX = Date::MAX;

    public const DEFAULT_FORMAT = 'Y-m-d| - Y-m-d';

    private Date $start;

    private Date $end;

    final public function __construct(Date $start, Date $end)
    {
        static::validate($start, $end);

        if ($start->equals($end)) {
            // canonical empty interval
            $this->start = new Date(static::MAX);
            $this->end = new Date(static::MIN);
        } else {
            $this->start = $start;
            $this->end = $end;
        }
    }

    public static function validate(Date $start, Date $end): void
    {
        if ($start->isAfter($end)) {
            throw new InvalidIntervalStartEndOrderException($start, $end);
        }

        $min = new Date(self::MIN);
        $max = new Date(self::MAX);
        if ($start->isBefore($min)) {
            throw new ValueOutOfAllowedRangeException($start, $min, $max);
        }
        if ($end->isAfter($max)) {
            throw new ValueOutOfAllowedRangeException($end, $min, $max);
        }
    }

    public static function createFromDateInterval(DateInterval $interval): static
    {
        if ($interval->isEmpty()) {
            return static::empty();
        }

        return new static($interval->getStart(), $interval->getEnd()->addDay());
    }

    public static function createFromString(string $string): static
    {
        [$start, $end, $openStart, $openEnd] = IntervalParser::parseString($string);

        $start = new Date($start);
        $end = new Date($end);
        if ($openStart) {
            $start = $start->addDay();
        }
        if ($openEnd) {
            $end = $end->subtractDay();
        }

        $startJd = $start->getJulianDay();
        $endJd = $end->getJulianDay();

        if ($startJd > $endJd) {
            throw new InvalidIntervalStartEndOrderException($start, $end);
        } elseif ($startJd === $endJd) {
            return self::empty();
        } else {
            return new static($start, $end);
        }
    }

    public static function createFromStartAndLength(Date $start, DateTimeUnit $unit, int $amount): static
    {
        if (!$unit->isDate()) {
            throw new InvalidDateTimeUnitException($unit);
        }
        if ($unit->equalsValue(DateTimeUnit::QUARTER)) {
            $unit = DateTimeUnit::month();
            $amount *= 3;
        }

        return new static($start, $start->modify('+' . $amount . ' ' . $unit->getValue() . ' -1 day'));
    }

    public static function future(?TimeProvider $timeProvider = null): static
    {
        $tomorrow = $timeProvider?->getDate() ?? new Date();

        return new static($tomorrow, new Date(static::MAX));
    }

    public static function past(?TimeProvider $timeProvider = null): static
    {
        $yesterday = $timeProvider?->getDate() ?? new Date();

        return new static(new Date(static::MIN), $yesterday);
    }

    public static function empty(): static
    {
        $interval = new static(new Date(static::MIN), new Date(static::MAX));
        $interval->start = new Date(static::MAX);
        $interval->end = new Date(static::MIN);

        return $interval;
    }

    public static function all(): static
    {
        return new static(new Date(static::MIN), new Date(static::MAX));
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        $this->format();
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): static
    {
        return new static($this->start->modify($value), $this->end->modify($value));
    }

    public function setStart(Date $start): static
    {
        return new static($start, $this->end);
    }

    public function setEnd(Date $end): static
    {
        return new static($this->start, $end);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getDateSpan(): DateSpan
    {
        return DateSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getLengthInDays(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getJulianDay() - $this->start->getJulianDay();
    }

    public function getNightsCount(): int
    {
        return $this->getLengthInDays();
    }

    public function toDateInterval(): DateInterval
    {
        if ($this->start > $this->end) {
            return DateInterval::empty();
        }

        return new DateInterval($this->start, $this->end->subtractDay());
    }

    public function toDateTimeInterval(Time $startTime, Time $endTime, ?DateTimeZone $timeZone = null): DateTimeInterval
    {
        return new DateTimeInterval(
            $this->start->getStart($timeZone)->setTime($startTime),
            $this->end->getStart($timeZone)->setTime($endTime)
        );
    }

    public function toDayNumberIntInterval(): IntInterval
    {
        return new IntInterval($this->start->getJulianDay(), $this->end->getJulianDay());
    }

    /**
     * @return array<Date>
     */
    public function toDateArray(): array
    {
        return $this->toDateInterval()->toDateArray();
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this, $format);
    }

    public function getStart(): Date
    {
        return $this->start;
    }

    public function getEnd(): Date
    {
        return $this->end;
    }

    /**
     * @return array<Date>
     */
    public function getStartEnd(): array
    {
        return [$this->start, $this->end];
    }

    public function isEmpty(): bool
    {
        return $this->start->getJulianDay() > $this->end->getJulianDay();
    }

    /**
     * @param self $other
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->start->equals($other->start) && $this->end->equals($other->end);
    }

    /**
     * @param self $other
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start) ?: $this->end->compare($other->end);
    }

    /**
     * @param self $other
     */
    public function compareIntersects(IntersectComparable $other): int
    {
        Check::instance($other, self::class);

        return IntervalCalc::compareIntersects(
            $this->start->getJulianDay(),
            $this->end->getJulianDay() - 1,
            $other->start->getJulianDay(),
            $other->end->getJulianDay() - 1
        );
    }

    public function containsValue(Date|DateTimeInterface $date): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        if (!$date instanceof Date) {
            $date = Date::createFromDateTimeInterface($date);
        }

        return $date->isBetween($this->start, $this->end->subtractDay());
    }

    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        return $this->start->isSameOrBefore($interval->start) && $this->end->isSameOrAfter($interval->end);
    }

    public function intersects(self $interval): bool
    {
        return $this->start->isBefore($interval->end) && $this->end->isAfter($interval->start);
    }

    public function touches(self $interval): bool
    {
        return $this->start->equals($interval->end) || $this->end->equals($interval->start);
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): NightIntervalSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new NightIntervalSet([]);
        }

        $partSize = ($this->end->getJulianDay() - $this->start->getJulianDay()) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            $intervalStarts[] = (int) round($this->start->getJulianDay() + $partSize * $n);
        }
        $intervalStarts = array_unique($intervalStarts);
        $intervalStarts = Arr::map($intervalStarts, static function (int $julianDay): Date {
            return Date::createFromJulianDay($julianDay);
        });

        return $this->splitBy($intervalStarts);
    }

    /**
     * @param array<Date> $intervalStarts
     */
    public function splitBy(array $intervalStarts): NightIntervalSet
    {
        if ($this->isEmpty()) {
            return new NightIntervalSet([]);
        }

        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        foreach ($intervalStarts as $intervalStart) {
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart) && $interval->containsValue($intervalStart->subtractDay())) {
                $results[$i] = new static($interval->start, $intervalStart);
                $results[] = new static($intervalStart, $interval->end);
                $i++;
            }
        }

        return new NightIntervalSet($results);
    }

    public function envelope(self ...$items): static
    {
        $items[] = $this;
        $start = Date::MAX_DAY_NUMBER;
        $end = Date::MIN_DAY_NUMBER;
        foreach ($items as $item) {
            $startValue = $item->start->getJulianDay();
            if ($startValue < $start) {
                $start = $startValue;
            }
            $endValue = $item->end->getJulianDay();
            if ($endValue > $end) {
                $end = $endValue;
            }
        }

        return new static(new Date($start), new Date($end));
    }

    public function intersect(self ...$items): static
    {
        $items[] = $this;
        $sorted = Arr::sortComparable($items);

        /** @var static $result */
        $result = array_shift($sorted);
        foreach ($sorted as $item) {
            if ($result->end->isAfter($item->start)) {
                $result = new static(Date::max($result->start, $item->start), Date::min($result->end, $item->end));
            } else {
                return static::empty();
            }
        }

        return $result;
    }

    public function union(self ...$items): NightIntervalSet
    {
        $items[] = $this;
        $sorted = Arr::sortComparable($items);

        $current = array_shift($sorted);
        $results = [$current];
        foreach ($sorted as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($current->end->isAfter($item->start->subtractDay())) {
                $current = new static($current->start, Date::max($current->end, $item->end));
                $results[count($results) - 1] = $current;
            } else {
                $current = $item;
                $results[] = $current;
            }
        }

        return new NightIntervalSet($results);
    }

    public function difference(self ...$items): NightIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new NightIntervalSet($results);
    }

    public function subtract(self ...$items): NightIntervalSet
    {
        $intervals = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            foreach ($intervals as $i => $interval) {
                unset($intervals[$i]);
                if ($interval->start->isBefore($item->start) && $interval->end->isAfter($item->end)) {
                    $intervals[] = new static($interval->start, $item->start);
                    $intervals[] = new static($item->end, $interval->end);
                } elseif ($interval->start->isBefore($item->start)) {
                    $intervals[] = new static($interval->start, Date::min($interval->end, $item->start));
                } elseif ($interval->end->isAfter($item->end)) {
                    $intervals[] = new static(Date::max($interval->start, $item->end), $interval->end);
                }
            }
        }

        return new NightIntervalSet(array_values($intervals));
    }

    public function invert(): NightIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @return array<array{0: NightInterval, 1: int}> ($interval, $count)
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        foreach ($overlaps as $overlap) {
            $ident = $overlap->toDayNumberIntInterval()->format();
            if (isset($results[$ident])) {
                $results[$ident][1]++;
            } else {
                $results[$ident] = [$overlap, 1];
            }
        }

        return array_values($results);
    }

    /**
     * @return array<NightInterval>
     */
    public static function explodeOverlaps(self ...$items): array
    {
        $sorted = Arr::sortComparable($items);
        $starts = array_fill(0, count($sorted), 0);
        $i = 0;
        while (isset($sorted[$i])) {
            $a = $sorted[$i];
            if ($a->isEmpty()) {
                unset($sorted[$i]);
                $i++;
                continue;
            }
            foreach ($sorted as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                } elseif ($a->end->isSameOrBefore($b->start) || $a->start->isSameOrAfter($b->end)) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($a->start->equals($b->start)) {
                    if ($a->end->isAfter($b->end)) {
                        // a1=b1----b2----a2
                        $sorted[$i] = $b;
                        $sorted[] = new static($b->end, $a->end);
                        $starts[count($sorted) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2=b2
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($a->start->isBefore($b->start)) {
                    if ($a->end->equals($b->end)) {
                        // a1----b1----a2=b2
                        $sorted[$i] = $b;
                        $sorted[] = new static($a->start, $b->start);
                        $starts[count($sorted) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end->isAfter($b->end)) {
                        // a1----b1----b2----a2
                        $sorted[$i] = $b;
                        $sorted[] = new static($a->start, $b->start);
                        $starts[count($sorted) - 1] = $i + 1;
                        $sorted[] = new static($b->end, $a->end);
                        $starts[count($sorted) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end);
                        $sorted[$i] = $new;
                        $sorted[] = new static($a->start, $b->start);
                        $starts[count($sorted) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end->isAfter($b->end)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end);
                        $sorted[$i] = $new;
                        $sorted[] = new static($b->end, $a->end);
                        $starts[count($sorted) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2=b2
                        // b1----a1----a2----b2
                        continue;
                    }
                }
            }
            $i++;
        }

        return array_values(Arr::sortComparable($sorted));
    }

    /**
     * @param array<self> $intervals
     * @return array<self>
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $intervals): array
    {
        return Arr::sortComparable($intervals);
    }

    /**
     * @param array<self> $intervals
     * @return array<self>
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sortByStart(array $intervals): array
    {
        return Arr::sortComparable($intervals);
    }

}
