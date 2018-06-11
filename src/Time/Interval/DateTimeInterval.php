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
use Dogma\Math\Interval\IntInterval;
use Dogma\Math\Interval\OpenClosedInterval;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use Dogma\Time\InvalidIntervalException;
use Dogma\Time\Span\DateTimeSpan;

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

    public function __construct(DateTime $start, DateTime $end, bool $openStart = false, bool $openEnd = false)
    {
        $this->start = $start;
        $this->end = $end;
        $this->openStart = $openStart;
        $this->openEnd = $openEnd;

        if ($this->start > $this->end) {
            throw new InvalidIntervalException($this->start, $this->end);
        }

        if ($start->equals($end) && ($openStart || $openEnd)) {
            // default createEmpty()
            $this->start = new DateTime(self::MAX);
            $this->end = new DateTime(self::MIN);
            $this->openStart = $this->openEnd = false;
        }
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

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function toTimestampIntInterval(): IntInterval
    {
        return new IntInterval($this->start->getTimestamp(), $this->end->getTimestamp());
    }

    public function toMicroTimestampIntInterval(): IntInterval
    {
        return new IntInterval($this->start->getMicroTimestamp(), $this->end->getMicroTimestamp());
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
        return $this->start > $this->end || ($this->start->equals($this->end) && $this->openStart && $this->openEnd);
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        $other instanceof self || Check::object($other, self::class);

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
        $other instanceof self || Check::object($other, self::class);

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
        return $this->containsValue($interval->start) || $this->containsValue($interval->end) || $interval->containsValue($this->start) || $interval->containsValue($this->end);
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

    public function split(int $parts, int $splitMode = self::SPLIT_CLOSED): DateTimeIntervalSet
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
     * @param \Dogma\Time\DateTime $intervalStarts
     * @param int $splitMode
     * @return \Dogma\Time\Interval\DateTimeIntervalSet
     */
    public function splitBy(array $intervalStarts, int $splitMode = self::SPLIT_CLOSED): DateTimeIntervalSet
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
        foreach ($overlaps as $i => [$item, $count]) {
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
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start, $interval->openStart, !$item->openStart);
                    }
                } elseif ($endHigher) {
                    if ($interval->start > $item->end || ($interval->start->equals($item->end) && $interval->openStart && !$item->openEnd)) {
                        // i1----i2    r1****r2
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
                } elseif ($a->start->equals($b->start) && $a->openStart === $b->openStart) {
                    if ($a->end->equals($b->end) && $a->openEnd === $b->openEnd) {
                        // a1=b1----a2=b2
                    } elseif ($a->end > $b->end || ($a->end->equals($b->end) && $a->openEnd === false)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2----b2
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
                    } elseif ($a->end > $b->end || ($a->end->equals($b->end) && $a->openEnd === false)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end, $a->openStart, $b->openEnd);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end, !$b->openEnd, $a->openEnd);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2----b2
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
        return Arr::sortWith($intervals, function (DateTimeInterval $a, DateTimeInterval $b) {
            return $a->start->getMicroTimestamp() <=> $b->start->getMicroTimestamp()
                ?: $a->end->getMicroTimestamp() <=> $b->end->getMicroTimestamp();
        });
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        return Arr::sortWith($intervals, function (DateTimeInterval $a, DateTimeInterval $b) {
            return $a->start->getMicroTimestamp() <=> $b->start->getMicroTimestamp();
        });
    }

}
