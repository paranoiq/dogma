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
use Dogma\Math\Interval\IntervalParser;
use Dogma\Math\Interval\OpenClosedInterval;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\InvalidTimeIntervalException;
use Dogma\Time\Microseconds;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Span\TimeSpan;
use Dogma\Time\Time;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function round;
use function usort;

/**
 * Interval of times without date.
 *
 * Start time (and end time in the same day) is automatically normalized to 00:00-23:59.
 * End time if it is after midnight (that means lower value than start time), will be automatically normalized to 24:00-47:59.
 *
 * Span between start and end of interval cannot be more than 24 hours.
 */
class TimeInterval implements DateOrTimeInterval, OpenClosedInterval, Pokeable
{
    use StrictBehaviorMixin;

    public const MIN = Time::MIN;
    public const MAX = '47:59:59.999999';

    public const DEFAULT_FORMAT = 'H:i:s.u| - H:i:s.u';

    /** @var \Dogma\Time\Time */
    private $start;

    /** @var \Dogma\Time\Time */
    private $end;

    /** @var bool */
    private $openStart = false;

    /** @var bool */
    private $openEnd = false;

    public function __construct(Time $start, Time $end, bool $openStart = false, bool $openEnd = true)
    {
        $startTime = $start->getMicroTime();
        $endTime = $end->getMicroTime();

        if ($startTime >= Microseconds::DAY) {
            $startTime %= Microseconds::DAY;
            $start = $start->normalize();
        }
        if ($endTime > Microseconds::DAY) {
            $endTime %= Microseconds::DAY;
            $end = $end->normalize();
        }
        if ($startTime > $endTime) {
            $endTime += Microseconds::DAY;
            $end = $end->denormalize();
        }

        $length = $endTime - $startTime;
        if ($length > Microseconds::DAY) {
            throw new InvalidTimeIntervalException($start, $end);
        }

        $this->start = $start;
        $this->end = $end;
        $this->openStart = $openStart;
        $this->openEnd = $openEnd;
    }

    public static function createFromString(string $string): self
    {
        [$start, $end, $openStart, $openEnd] = IntervalParser::parseString($string);

        $start = new Time($start);
        $end = new Time($end);

        return new static($start, $end, $openStart ?? false, $openEnd ?? true);
    }

    public static function createFromStartAndLength(Time $start, DateTimeUnit $unit, int $amount, bool $openStart = false, bool $openEnd = true): self
    {
        if (!$unit->isTime()) {
            throw new InvalidDateTimeUnitException($unit);
        }
        if ($unit === DateTimeUnit::milisecond()) {
            $unit = DateTimeUnit::microsecond();
            $amount *= 1000;
        }

        return new static($start, $start->modify('+' . $amount . ' ' . $unit->getValue()), $openStart, $openEnd);
    }

    public static function closed(Time $start, Time $end): self
    {
        return new static($start, $end, false, false);
    }

    public static function open(Time $start, Time $end): self
    {
        return new static($start, $end, true, true);
    }

    public static function openStart(Time $start, Time $end): self
    {
        return new static($start, $end, true, false);
    }

    public static function openEnd(Time $start, Time $end): self
    {
        return new static($start, $end, false, true);
    }

    public static function empty(): self
    {
        return new static(new Time(self::MIN), new Time(self::MIN), true, true);
    }

    public static function all(): self
    {
        return new static(new Time(self::MIN), new Time(self::MAX), false, false);
    }

    public function poke(): void
    {
        $this->start->format();
        $this->end->format();
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value), $this->openStart, $this->openEnd);
    }

    public function setStart(Time $start, ?bool $open = null): self
    {
        return new static($start, $this->end, $open ?? $this->openStart, $this->openEnd);
    }

    public function setEnd(Time $end, ?bool $open = null): self
    {
        return new static($this->start, $end, $this->openStart, $open ?? $this->openEnd);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getTimeSpan(): TimeSpan
    {
        return TimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getLengthInMicroseconds(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getMicroTime() - $this->start->getMicroTime();
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this, $format);
    }

    public function getStart(): Time
    {
        return $this->start;
    }

    public function getEnd(): Time
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
        return ($this->openStart === true || $this->openEnd === true)
            && $this->start->getMicroTime() === $this->end->getMicroTime();
    }

    public function isOverMidnight(): bool
    {
        return $this->end->getMicroTime() >= Time::MAX_MICROSECONDS;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->start->equals($other->start)
            && $this->end->getMicroTime() === $other->end->getMicroTime() // cannot use Time::equals() because of 00:00 vs 24:00
            && $this->openStart === $other->openStart
            && $this->openEnd === $other->openEnd;
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start)
            ?: $this->end->getMicroTime() <=> $other->end->getMicroTime() // cannot use Time::compare() because of 00:00 vs 24:00
            ?: $this->openEnd <=> $other->openEnd;
    }

    public function containsValue(Time $value): bool
    {
        $time = $value->normalize()->getMicroTime();
        $time2 = $value->denormalize()->getMicroTime();
        $startTime = $this->getStart()->getMicroTime();
        $endTime = $this->getEnd()->getMicroTime();

        return (($this->openStart ? $time > $startTime : $time >= $startTime) && ($this->openEnd ? $time < $endTime : $time <= $endTime))
            || (($this->openStart ? $time2 > $startTime : $time2 >= $startTime) && ($this->openEnd ? $time2 < $endTime : $time2 <= $endTime));
    }

    /**
     * @param \Dogma\Time\Interval\TimeInterval $interval
     * @return bool
     */
    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        $intervalStart = $interval->start->getMicroTime();
        $intervalEnd = $interval->getEnd()->getMicroTime();
        $thisStart = $this->getStart()->getMicroTime();
        $thisEnd = $this->getEnd()->getMicroTime();

        return (($this->openStart && !$interval->openStart) ? $intervalStart > $thisStart : $intervalStart >= $thisStart)
            && (($this->openEnd && !$interval->openEnd) ? $intervalEnd < $thisEnd : $intervalEnd <= $thisEnd);
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
     * @param \Dogma\Time\Interval\TimeInterval $interval
     * @param bool $exclusive
     * @return bool
     */
    public function touches(self $interval, bool $exclusive = false): bool
    {
        return ($this->start->getMicroTime() === $interval->end->getMicroTime() && ($exclusive ? ($this->openStart xor $interval->openEnd) : true))
            || ($this->end->getMicroTime() === $interval->start->getMicroTime() && ($exclusive ? ($this->openEnd xor $interval->openStart) : true));
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts, int $splitMode = self::SPLIT_OPEN_ENDS): TimeIntervalSet
    {
        if ($this->isEmpty()) {
            return new TimeIntervalSet([]);
        }

        $partSize = ($this->end->getMicroTime() - $this->start->getMicroTime()) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            // rounded to microseconds
            $intervalStarts[] = round(($this->start->getMicroTime() + $partSize * $n) % (Time::MAX_MICROSECONDS + 1), 6);
        }
        $intervalStarts = array_unique($intervalStarts);
        $intervalStarts = Arr::map($intervalStarts, function (int $timestamp) {
            return new Time($timestamp);
        });

        return $this->splitBy($intervalStarts, $splitMode);
    }

    /**
     * @param \Dogma\Time\Time[] $intervalStarts
     * @param int $splitMode
     * @return \Dogma\Time\Interval\TimeIntervalSet
     */
    public function splitBy(array $intervalStarts, int $splitMode = self::SPLIT_OPEN_ENDS): TimeIntervalSet
    {
        if ($this->isEmpty()) {
            return new TimeIntervalSet([]);
        }

        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        foreach ($intervalStarts as $intervalStart) {
            /** @var \Dogma\Time\Interval\TimeInterval $interval */
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart)) {
                $results[$i] = new static($interval->start, $intervalStart, $interval->openStart, $splitMode === self::SPLIT_OPEN_ENDS ? self::OPEN : self::CLOSED);
                $results[] = new static($intervalStart, $interval->end, $splitMode === self::SPLIT_OPEN_STARTS ? self::OPEN : self::CLOSED, $interval->openEnd);
                $i++;
            }
        }

        return new TimeIntervalSet($results);
    }

    /**
     * @return self[]
     */
    public function splitByMidnight(): array /// mode
    {
        if (!$this->isOverMidnight()) {
            return [$this, self::empty()];
        }

        return [
            new self($this->start, new Time(Time::MAX_MICROSECONDS), $this->openStart, false),
            new self(new Time(Time::MIN_MICROSECONDS), $this->end, false, $this->openEnd),
        ];
    }

    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = new Time(self::MAX);
        $end = new Time(self::MIN);
        $startExclusive = true;
        $endExclusive = true;
        /** @var \Dogma\Time\Interval\TimeInterval $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($item->start->getMicroTime() < $start->getMicroTime()) {
                $start = $item->start;
                $startExclusive = $item->openStart;
            } elseif ($startExclusive && !$item->openStart && $item->start->equals($start)) {
                $startExclusive = false;
            }
            if ($item->end->getMicroTime() > $end->getMicroTime()) {
                $end = $item->end;
                $endExclusive = $item->openEnd;
            } elseif ($endExclusive && !$item->openEnd && $item->end->getMicroTime() === $end->getMicroTime()) {
                $endExclusive = false;
            }
        }

        return new static($start, $end, $startExclusive, $endExclusive);
    }

    public function intersect(self ...$items): self
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        /** @var \Dogma\Time\Interval\TimeInterval $result */
        $result = array_shift($items);
        /** @var \Dogma\Time\Interval\TimeInterval $item */
        foreach ($items as $item) {
            if ($result->start->getMicroTime() < $item->start->getMicroTime() || ($result->start->equals($item->start) && $result->openStart && !$item->openStart)) {
                if ($result->end->getMicroTime() < $item->start->getMicroTime() || ($result->end->getMicroTime() === $item->start->getMicroTime() && ($result->openEnd || $item->openStart))) {
                    return self::empty();
                }
                $result = new static($item->start, $result->end, $item->openStart, $result->openEnd);
            }
            if ($result->end->getMicroTime() > $item->end->getMicroTime() || ($result->end->getMicroTime() === $item->end->getMicroTime() && !$result->openEnd && $item->openEnd)) {
                if ($result->start->getMicroTime() > $item->end->getMicroTime() || ($result->start->getMicroTime() === $item->end->getMicroTime() && ($result->openStart || $item->openEnd))) {
                    return self::empty();
                }
                $result = new static($result->start, $item->end, $result->openStart, $item->openEnd);
            }
        }

        return $result;
    }

    public function union(self ...$items): TimeIntervalSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var \Dogma\Time\Interval\TimeInterval $item */
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

        return new TimeIntervalSet($results);
    }

    public function difference(self ...$items): TimeIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new TimeIntervalSet($results);
    }

    public function subtract(self ...$items): TimeIntervalSet
    {
        $results = [$this];

        /** @var \Dogma\Time\Interval\TimeInterval $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            $itemStartTime = $item->getStart()->getMicroTime();
            $itemEndTime = $item->getEnd()->getMicroTime();
            /** @var \Dogma\Time\Interval\TimeInterval $interval */
            foreach ($results as $r => $interval) {
                $intervalStartTime = $interval->getStart()->getMicroTime();
                $intervalEndTime = $interval->getEnd()->getMicroTime();

                $startLower = $intervalStartTime < $itemStartTime || ($intervalStartTime === $itemStartTime && !$interval->openStart && $item->openStart);
                $endHigher = $intervalEndTime > $itemEndTime || ($intervalEndTime === $itemEndTime && $interval->openEnd && !$item->openEnd);
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    $results[] = new static($item->end, $interval->end, !$item->openEnd, $interval->openEnd);
                } elseif ($startLower) {
                    if ($intervalEndTime < $itemStartTime || ($intervalEndTime === $itemStartTime && $interval->openEnd && !$item->openStart)) {
                        // r1****r2    i1----i2
                        continue;
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    }
                } elseif ($endHigher) {
                    if ($intervalStartTime > $itemEndTime || ($intervalStartTime === $itemEndTime && $interval->openStart && !$item->openEnd)) {
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

        return new TimeIntervalSet(array_values($results));
    }

    public function invert(): TimeIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Time\Interval\TimeInterval ...$items
     * @return \Dogma\Time\Interval\TimeInterval[][]|int[][] ($interval, $count)
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Time\Interval\TimeInterval $overlap */
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
     * @param \Dogma\Time\Interval\TimeInterval ...$items
     * @return \Dogma\Time\Interval\TimeInterval[]
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
            $aStartTime = $a->getStart()->getMicroTime();
            $aEndTime = $a->getEnd()->getMicroTime();
            /** @var \Dogma\Time\Interval\TimeInterval $b */
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                }
                $bStartTime = $b->getStart()->getMicroTime();
                $bEndTime = $b->getEnd()->getMicroTime();
                if ($aEndTime < $bStartTime || ($aEndTime === $bStartTime && ($a->openEnd || $b->openStart))
                    || $aStartTime > $bEndTime || ($aStartTime === $bEndTime && ($a->openStart || $b->openEnd))) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($aStartTime === $bStartTime && $a->openStart === $b->openStart) {
                    if ($aEndTime === $bEndTime && $a->openEnd === $b->openEnd) {
                        // a1=b1----a2=b2
                        continue;
                    } elseif ($aEndTime > $bEndTime || ($aEndTime === $bEndTime && $a->openEnd === false)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } else {
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($aStartTime < $bStartTime || ($aStartTime === $bStartTime && $a->openStart === false)) {
                    if ($aEndTime === $bEndTime && $a->openEnd === $b->openEnd) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } elseif ($aEndTime > $bEndTime || ($aEndTime === $bEndTime && $a->openEnd === false)) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end, $b->openStart, $a->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start, $a->openStart, !$b->openStart);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
                    }
                } else {
                    if ($aEndTime === $bEndTime && $a->openEnd === $b->openEnd) {
                        // b1----a1----a2=b2
                        continue;
                    } elseif ($aEndTime > $bEndTime || ($aEndTime === $bEndTime && $a->openEnd === false)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end, $a->openStart, $b->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                        $aStartTime = $a->getStart()->getMicroTime();
                        $aEndTime = $a->getEnd()->getMicroTime();
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
        usort($intervals, function (TimeInterval $a, TimeInterval $b) {
            return $a->start->getMicroTime() <=> $b->start->getMicroTime() ?: $a->end->getMicroTime() <=> $b->end->getMicroTime();
        });

        return $intervals;
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        usort($intervals, function (TimeInterval $a, TimeInterval $b) {
            return $a->start->getMicroTime() <=> $b->start->getMicroTime();
        });

        return $intervals;
    }

}
