<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\Math\IntCalc;
use Dogma\Math\Interval\IntervalParser;
use Dogma\Math\Interval\OpenClosedInterval;
use Dogma\NotImplementedException;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Provider\TimeProvider;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Time;
use Dogma\Time\TimeCalc;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function range;
use function round;
use function usort;

/**
 * Interval of times including date. Based on FloatInterval.
 */
class DateTimeInterval implements DateOrTimeInterval, OpenClosedInterval
{
    use StrictBehaviorMixin;

    public const MIN = DateTime::MIN;
    public const MAX = DateTime::MAX;

    public const DEFAULT_FORMAT = 'Y-m-d H:i:s| - Y-m-d H:i:s';

    /** @var \Dogma\Time\DateTime */
    private $start;

    /** @var \Dogma\Time\DateTime */
    private $end;

    /** @var bool */
    private $openStart;

    /** @var bool */
    private $openEnd;

    public function __construct(DateTime $start, DateTime $end, bool $openStart = false, bool $openEnd = true)
    {
        if ($start > $end) {
            throw new InvalidIntervalStartEndOrderException($start, $end);
        }

        $this->start = $start;
        $this->end = $end;
        $this->openStart = $openStart;
        $this->openEnd = $openEnd;

        if ($start->equals($end) && ($openStart || $openEnd)) {
            // default createEmpty()
            $this->start = new DateTime(self::MAX);
            $this->end = new DateTime(self::MIN);
            $this->openStart = $this->openEnd = false;
        }
    }

    public static function createFromString(string $string): self
    {
        [$start, $end, $openStart, $openEnd] = IntervalParser::parseString($string);

        $start = new DateTime($start);
        $end = new DateTime($end);

        return new static($start, $end, $openStart ?? false, $openEnd ?? true);
    }

    public static function createFromStartAndLength(DateTime $start, DateTimeUnit $unit, int $amount, bool $openStart = false, bool $openEnd = true): self
    {
        if ($unit === DateTimeUnit::quarter()) {
            $unit = DateTimeUnit::month();
            $amount *= 3;
        } elseif ($unit === DateTimeUnit::milisecond()) {
            $unit = DateTimeUnit::microsecond();
            $amount *= 1000;
        }

        return new static($start, $start->modify('+' . $amount . ' ' . $unit->getValue()), $openStart, $openEnd);
    }

    public static function createFromDateAndTimeInterval(Date $date, TimeInterval $timeInterval, ?\DateTimeZone $timeZone = null): self
    {
        return new static(
            DateTime::createFromDateAndTime($date, $timeInterval->getStart(), $timeZone),
            DateTime::createFromDateAndTime($date, $timeInterval->getEnd(), $timeZone),
            $timeInterval->hasOpenStart(),
            $timeInterval->hasOpenEnd()
        );
    }

    public static function createFromDateIntervalAndTime(DateInterval $dateInterval, Time $time, ?\DateTimeZone $timeZone = null, bool $openStart = false, bool $openEnd = true): self
    {
        return new static(
            DateTime::createFromDateAndTime($dateInterval->getStart(), $time, $timeZone),
            DateTime::createFromDateAndTime($dateInterval->getEnd(), $time, $timeZone),
            $openStart,
            $openEnd
        );
    }

    public static function future(?\DateTimeZone $timeZone = null, ?TimeProvider $timeProvider = null): self
    {
        $now = $timeProvider !== null ? $timeProvider->getDateTime($timeZone) : new DateTime('now', $timeZone);

        return new static($now, new DateTime(self::MAX, $timeZone), true, false);
    }

    public static function past(?\DateTimeZone $timeZone = null, ?TimeProvider $timeProvider = null): self
    {
        $now = $timeProvider !== null ? $timeProvider->getDateTime($timeZone) : new DateTime('now', $timeZone);

        return new static(new DateTime(self::MIN, $timeZone), $now, false, true);
    }

    public static function closed(DateTime $start, DateTime $end): self
    {
        return new static($start, $end, false, false);
    }

    public static function open(DateTime $start, DateTime $end): self
    {
        return new static($start, $end, true, true);
    }

    public static function openStart(DateTime $start, DateTime $end): self
    {
        return new static($start, $end, true);
    }

    public static function openEnd(DateTime $start, DateTime $end): self
    {
        return new static($start, $end, false, true);
    }

    public static function empty(): self
    {
        $interval = new static(new DateTime(), new DateTime());
        $interval->start = new DateTime(self::MAX);
        $interval->end = new DateTime(self::MIN);

        return $interval;
    }

    public static function all(): self
    {
        return new static(new DateTime(self::MIN), new DateTime(self::MAX));
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value), $this->openStart, $this->openEnd);
    }

    public function setStart(DateTime $start, ?bool $open = null): self
    {
        return new static($start, $this->end, $open ?? $this->openStart, $this->openEnd);
    }

    public function setEnd(DateTime $end, ?bool $open = null): self
    {
        return new static($this->start, $end, $this->openStart, $open ?? $this->openEnd);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function toDateInterval(): DateInterval
    {
        if ($this->isEmpty()) {
            return DateInterval::empty();
        }

        $start = $this->openStart && $this->start->getTime()->getMicroTime() === Time::MAX_MICROSECONDS
            ? $this->start->getDate()->addDay()
            : $this->start->getDate();
        $end = $this->openEnd && $this->end->getTime()->getMicroTime() === Time::MIN_MICROSECONDS
            ? $this->end->getDate()->subtractDay()
            : $this->end->getDate();

        return new DateInterval($start, $end);
    }

    public function getLengthInMicroseconds(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getMicroTimestamp() - $this->start->getMicroTimestamp();
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this, $format);
    }

    public function getStart(): DateTime
    {
        return $this->start;
    }

    public function getEnd(): DateTime
    {
        return $this->end;
    }

    public function hasOpenStart(): bool
    {
        return $this->openStart;
    }

    public function hasOpenEnd(): bool
    {
        return $this->openEnd;
    }

    public function isEmpty(): bool
    {
        return $this->start > $this->end
            || ($this->start->equals($this->end) && $this->openStart && $this->openEnd);
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return ($this->start->equals($other->start)
            && $this->end->equals($other->end)
            && $this->openStart === $other->openStart
            && $this->openEnd === $other->openEnd)
            || ($this->isEmpty() && $other->isEmpty());
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start) ?: $this->end->compare($other->end);
    }

    public function containsValue(DateTime $value): bool
    {
        return ($this->openStart ? $value > $this->start : $value >= $this->start)
            && ($this->openEnd ? $value < $this->end : $value <= $this->end);
    }

    public function containsDateTime(\DateTimeInterface $value): bool
    {
        return $this->containsValue(DateTime::createFromDateTimeInterface($value));
    }

    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }
        return (($this->openStart && !$interval->openStart) ? $interval->start > $this->start : $interval->start >= $this->start)
            && (($this->openEnd && !$interval->openEnd) ? $interval->end < $this->end : $interval->end <= $this->end);
    }

    public function intersects(self $interval): bool
    {
        return $this->containsValue($interval->start)
            || $this->containsValue($interval->end)
            || $interval->containsValue($this->start)
            || $interval->containsValue($this->end)
            || ($this->start->equals($interval->start) && $this->end->equals($interval->end));
    }

    /**
     * @param \Dogma\Time\Interval\DateTimeInterval $interval
     * @param bool $exclusive
     * @return bool
     */
    public function touches(self $interval, bool $exclusive = false): bool
    {
        return ($this->start->getMicroTimestamp() === $interval->end->getMicroTimestamp() && ($exclusive ? ($this->openStart xor $interval->openEnd) : true))
            || ($this->end->getMicroTimestamp() === $interval->start->getMicroTimestamp() && ($exclusive ? ($this->openEnd xor $interval->openStart) : true));
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts, int $splitMode = self::SPLIT_OPEN_ENDS): DateTimeIntervalSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new DateTimeIntervalSet([]);
        }

        $partSize = ($this->end->getMicroTimestamp() - $this->start->getMicroTimestamp() + 1) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            // rounded to microseconds
            $intervalStarts[] = round($this->start->getMicroTimestamp() + $partSize * $n, 6);
        }
        $intervalStarts = array_unique($intervalStarts); /// why unique???
        $intervalStarts = Arr::map($intervalStarts, function (int $timestamp) {
            return DateTime::createFromMicroTimestamp($timestamp, $this->getStart()->getTimezone());
        });

        return $this->splitBy($intervalStarts, $splitMode);
    }

    /**
     * @param \Dogma\Time\DateTime[] $intervalStarts
     * @param int $splitMode
     * @return \Dogma\Time\Interval\DateTimeIntervalSet
     */
    public function splitBy(array $intervalStarts, int $splitMode = self::SPLIT_OPEN_ENDS): DateTimeIntervalSet
    {
        if ($this->isEmpty()) {
            return new DateTimeIntervalSet([]);
        }

        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        foreach ($intervalStarts as $intervalStart) {
            /** @var \Dogma\Time\Interval\DateTimeInterval $interval */
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart)) {
                $results[$i] = new static($interval->start, $intervalStart, $interval->openStart, $splitMode === self::SPLIT_OPEN_ENDS ? self::OPEN : self::CLOSED);
                $results[] = new static($intervalStart, $interval->end, $splitMode === self::SPLIT_OPEN_STARTS ? self::OPEN : self::CLOSED, $interval->openEnd);
                $i++;
            }
        }

        return new DateTimeIntervalSet($results);
    }

    /**
     * Splits interval into smaller by increments of given unit from the beginning of interval.
     *
     * @param \Dogma\Time\DateTimeUnit $unit
     * @param int $amount
     * @param int $splitMode
     * @return \Dogma\Time\Interval\DateTimeIntervalSet
     */
    public function splitByUnit(DateTimeUnit $unit, int $amount = 1, int $splitMode = self::SPLIT_OPEN_ENDS): DateTimeIntervalSet
    {
        Check::positive($amount);

        $intervalStarts = [];
        $start = $this->start->addUnit($unit, $amount);
        while ($this->containsValue($start)) {
            $intervalStarts[] = $start;
            $start = $start->addUnit($unit, $amount);
        }

        return $this->splitBy($intervalStarts, $splitMode);
    }

    /**
     * Splits interval into parts with borders aligned to given reference or to a beginning of splitting unit.
     * eg. [2018-01-15 - 2018-02-15] split by 1 month will return two intervals:
     *  [2018-01-15 - 2018-01-31] and [2018-02-01 - 2018-02-15]
     *
     * When no reference is given, base for splitting will be calculated by rounding given unit* to a number divisible by given amount.
     * *) in context of a superior unit - number of month in year, iso number of week in year, number of day in month...
     *  eg. for 5 months beginning of May or October will be used as base.
     *
     * @param \Dogma\Time\DateTimeUnit $unit
     * @param int $amount
     * @param \Dogma\Time\DateTime|null $reference
     * @param int $splitMode
     * @return \Dogma\Time\Interval\DateTimeIntervalSet
     */
    public function splitByUnitAligned(DateTimeUnit $unit, int $amount = 1, ?DateTime $reference = null, int $splitMode = self::SPLIT_OPEN_ENDS): DateTimeIntervalSet
    {
        Check::positive($amount);

        if ($reference === null) {
            $reference = $this->createReference($unit, $amount);
        }

        $intervalStarts = [];
        $start = $reference->addUnit($unit, $amount);
        while ($this->containsValue($start)) {
            $intervalStarts[] = $start;
            $start = $start->addUnit($unit, $amount);
        }

        return $this->splitBy($intervalStarts, $splitMode);
    }

    private function createReference(DateTimeUnit $unit, int $amount): DateTime
    {
        switch ($unit->getValue()) {
            case DateTimeUnit::YEAR:
                $year = $this->start->getYear();
                if ($amount > 1) {
                    $year = IntCalc::roundDownTo($year, $amount);
                }

                return DateTime::createFromComponents($year, 1, 1, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::QUARTER:
                if ($amount > 1) {
                    throw new NotImplementedException('Behavior of quarters for amount larger than 1 is not defined.');
                }
                $month = IntCalc::roundDownTo($this->start->getMonth() - 1, 3) + 1;

                return DateTime::createFromComponents($this->start->getYear(), $month, 1, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::MONTH:
                $month = $this->start->getMonth();
                if ($amount > 1) {
                    $month = IntCalc::roundDownTo($month - 1, $amount) + 1;
                }

                return DateTime::createFromComponents($this->start->getYear(), $month, 1, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::WEEK:
                if ($amount > 1) {
                    $year = (int) $this->start->format('o');
                    $week = (int) $this->start->format('W');
                    $week = IntCalc::roundDownTo($week - 1, $amount) + 1;

                    return Date::createFromIsoYearAndWeek($year, $week, 1)->toDateTime($this->start->getTimezone());
                } else {
                    $dayOfWeek = $this->start->getDayOfWeek();

                    return $this->start->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0, 0, 0);
                }
            case DateTimeUnit::DAY:
                $day = $this->start->getDay();
                if ($amount > 1) {
                    $day = IntCalc::roundDownTo($day - 1, $amount) + 1;
                }

                return DateTime::createFromComponents($this->start->getYear(), $this->start->getMonth(), $day, 0, 0, 0, 0, $this->start->getTimezone());
            case DateTimeUnit::HOUR:
                $hours = null;
                if ($amount > 1) {
                    $hours = range(0, 23, $amount);
                }
                /** @var \Dogma\Time\DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $hours);

                return $reference;
            case DateTimeUnit::MINUTE:
            case DateTimeUnit::SECOND:
                $units = null;
                if ($amount > 1) {
                    $units = range(0, 59, $amount);
                }
                /** @var \Dogma\Time\DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $units);

                return $reference;
            case DateTimeUnit::MILISECOND:
                $miliseconds = null;
                if ($amount > 1) {
                    $miliseconds = range(0, 999, $amount);
                }
                /** @var \Dogma\Time\DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $miliseconds);

                return $reference;
            case DateTimeUnit::MICROSECOND:
                $microseconds = null;
                if ($amount > 1) {
                    $microseconds = range(0, 999999, $amount);
                }
                /** @var \Dogma\Time\DateTime $reference */
                $reference = TimeCalc::roundDownTo($this->start, $unit, $microseconds);

                return $reference;
            default:
                throw new ShouldNotHappenException('Unreachable');
        }
    }

    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = new DateTime(self::MAX);
        $end = new DateTime(self::MIN);
        $startExclusive = true;
        $endExclusive = true;
        /** @var \Dogma\Time\Interval\DateTimeInterval $item */
        foreach ($items as $item) {
            if ($item->start < $start) {
                $start = $item->start;
                $startExclusive = $item->openStart;
            } elseif ($startExclusive && !$item->openStart && $item->start->equals($start)) {
                $startExclusive = false;
            }
            if ($item->end > $end) {
                $end = $item->end;
                $endExclusive = $item->openEnd;
            } elseif ($endExclusive && !$item->openEnd && $item->end->equals($end)) {
                $endExclusive = false;
            }
        }

        return new static($start, $end, $startExclusive, $endExclusive);
    }

    public function intersect(self ...$items): self
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $result = array_shift($items);
        /** @var \Dogma\Time\Interval\DateTimeInterval $item */
        foreach ($items as $item) {
            if ($result->start < $item->start || ($result->start->equals($item->start) && $result->openStart && !$item->openStart)) {
                if ($result->end < $item->start || ($result->end->equals($item->start) && ($result->openEnd || $item->openStart))) {
                    return self::empty();
                }
                $result = new static($item->start, $result->end, $item->openStart, $result->openEnd);
            }
            if ($result->end > $item->end || ($result->end->equals($item->end) && !$result->openEnd && $item->openEnd)) {
                if ($result->start > $item->end || ($result->start->equals($item->end) && ($result->openStart || $item->openEnd))) {
                    return self::empty();
                }
                $result = new static($result->start, $item->end, $result->openStart, $item->openEnd);
            }
        }

        return $result;
    }

    public function union(self ...$items): DateTimeIntervalSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var \Dogma\Time\Interval\DateTimeInterval $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($current->intersects($item)) {
                $current = $current->envelope($item);
                $results[count($results) - 1] = $current;
            } else {
                $current = $item;
                $results[] = $current;
            }
        }

        return new DateTimeIntervalSet($results);
    }

    public function difference(self ...$items): DateTimeIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new DateTimeIntervalSet($results);
    }

    public function subtract(self ...$items): DateTimeIntervalSet
    {
        $results = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            /** @var \Dogma\Time\Interval\DateTimeInterval $interval */
            foreach ($results as $r => $interval) {
                $startLower = $interval->start < $item->start || ($interval->start->equals($item->start) && !$interval->openStart && $item->openStart);
                $endHigher = $interval->end > $item->end || ($interval->end->equals($item->end) && $interval->openEnd && !$item->openEnd);
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    $results[] = new static($item->end, $interval->end, !$item->openEnd, $interval->openEnd);
                } elseif ($startLower) {
                    if ($interval->end < $item->start || ($interval->end->equals($item->start) && $interval->openEnd && !$item->openStart)) {
                        // r1****r2    i1----i2
                        continue;
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    }
                } elseif ($endHigher) {
                    if ($interval->start > $item->end || ($interval->start->equals($item->end) && $interval->openStart && !$item->openEnd)) {
                        // i1----i2    r1****r2
                        continue;
                    } else {
                        // i1----r1----i2****r2
                        unset($results[$r]);
                        $results[] = new static($item->end, $interval->end, !$item->openEnd, $interval->openEnd);
                    }
                } else {
                    // i1----r1----r2----i2
                    unset($results[$r]);
                }
            }
        }

        return new DateTimeIntervalSet(array_values($results));
    }

    public function invert(): DateTimeIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Time\Interval\DateTimeInterval ...$items
     * @return \Dogma\Time\Interval\DateTimeInterval[][]|int[][] ($interval, $count)
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Time\Interval\DateTimeInterval $overlap */
        foreach ($overlaps as $overlap) {
            $ident = $overlap->format();
            if (isset($results[$ident])) {
                $results[$ident][1]++;
            } else {
                $results[$ident] = [$overlap, 1];
            }
        }

        return array_values($results);
    }

    /**
     * @param \Dogma\Time\Interval\DateTimeInterval ...$items
     * @return \Dogma\Time\Interval\DateTimeInterval[]
     */
    public static function explodeOverlaps(self ...$items): array
    {
        $items = self::sort($items);
        $starts = array_fill(0, count($items), 0);
        $i = 0;
        while (isset($items[$i])) {
            $a = $items[$i];
            if ($a->isEmpty()) {
                unset($items[$i]);
                $i++;
                continue;
            }
            /** @var \Dogma\Time\Interval\DateTimeInterval $b */
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                } elseif ($a->end < $b->start || ($a->end->equals($b->start) && ($a->openEnd || $b->openStart))
                    || $a->start > $b->end || ($a->start->equals($b->end) && ($a->openStart || $b->openEnd))) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($a->start->equals($b->start) && $a->openStart === $b->openStart) {
                    if ($a->end->equals($b->end) && $a->openEnd === $b->openEnd) {
                        // a1=b1----a2=b2
                        continue;
                    } elseif ($a->end > $b->end || ($a->end->equals($b->end) && $a->openEnd === false)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($a->start < $b->start || ($a->start->equals($b->start) && $a->openStart === false)) {
                    if ($a->end->equals($b->end) && $a->openEnd === $b->openEnd) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end > $b->end || ($a->end->equals($b->end) && $a->openEnd === false)) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end, $b->openStart, $a->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end->equals($b->end) && $a->openEnd === $b->openEnd) {
                        // b1----a1----a2=b2
                        continue;
                    } elseif ($a->end > $b->end || ($a->end->equals($b->end) && $a->openEnd === false)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end, $a->openStart, $b->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2----b2
                        continue;
                    }
                }
            }
            $i++;
        }

        return array_values(self::sort($items));
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sort(array $intervals): array
    {
        usort($intervals, function (DateTimeInterval $a, DateTimeInterval $b) {
            return $a->start->getMicroTimestamp() <=> $b->start->getMicroTimestamp()
                ?: $a->end->getMicroTimestamp() <=> $b->end->getMicroTimestamp();
        });

        return $intervals;
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        usort($intervals, function (DateTimeInterval $a, DateTimeInterval $b) {
            return $a->start->getMicroTimestamp() <=> $b->start->getMicroTimestamp();
        });

        return $intervals;
    }

}
