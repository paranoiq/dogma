<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Range;

use Dogma\Arr;
use Dogma\Check;
use Dogma\StrictBehaviorMixin;

class IntRange implements Range
{
    use StrictBehaviorMixin;

    public const MIN = PHP_INT_MIN;
    public const MAX = PHP_INT_MAX;

    /** @var int */
    private $start;

    /** @var int */
    private $end;

    public function __construct(int $start, int $end)
    {
        Check::min($end, $start);

        $this->start = $start;
        $this->end = $end;
    }

    public static function createEmpty(): self
    {
        $range = new static(0, 0);
        $range->start = self::MAX;
        $range->end = self::MIN;

        return $range;
    }

    public static function createAll(): self
    {
        return new static(self::MIN, self::MAX);
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(int $byValue): self
    {
        return new static($this->start + $byValue, $this->end + $byValue);
    }

    public function multiply(int $byValue): self
    {
        return new static($this->start * $byValue, $this->end * $byValue);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(): string
    {
        return sprintf('[%d, %d]', $this->start, $this->end);
    }

    public function getStart(): int
    {
        return $this->start;
    }

    public function getEnd(): int
    {
        return $this->end;
    }

    public function isEmpty(): bool
    {
        return $this->start > $this->end;
    }

    public function equals(self $range): bool
    {
        return $this->start === $range->start && $this->end === $range->end;
    }

    public function containsValue(int $value): bool
    {
        return $value >= $this->start && $value <= $this->end;
    }

    public function contains(self $range): bool
    {
        return $this->start <= $range->start && $this->end >= $range->end && !$range->isEmpty();
    }

    public function intersects(self $range): bool
    {
        return ($range->start >= $this->start && $range->start <= $this->end) || ($range->end >= $this->start && $range->end <= $this->end);
    }

    public function touches(self $range): bool
    {
        return $this->start === $range->end + 1 || $this->end === $range->start - 1;
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): IntRangeSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new IntRangeSet([$this]);
        }

        $partSize = ($this->end - $this->start + 1) / $parts;
        $borders = [];
        for ($n = 1; $n < $parts; $n++) {
            $borders[] = (int) round($this->start + $partSize * $n);
        }
        $borders = array_unique($borders);

        if ($borders === []) {
            return new IntRangeSet([$this]);
        }

        return $this->splitBy($borders);
    }

    /**
     * @param int[] $rangeStarts
     * @return \Dogma\Math\Range\IntRangeSet
     */
    public function splitBy(array $rangeStarts): IntRangeSet
    {
        $rangeStarts = Arr::sort($rangeStarts);
        $results = [$this];
        $i = 0;
        foreach ($rangeStarts as $rangeStart) {
            $range = $results[$i];
            if ($range->containsValue($rangeStart) && $range->containsValue($rangeStart - 1)) {
                $results[$i] = new static($range->start, $rangeStart - 1);
                $results[] = new static($rangeStart, $range->end);
                $i++;
            }
        }

        return new IntRangeSet($results);
    }

    // A1****A2****B1****B2 -> [A1, B2]
    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = self::MAX;
        $end = self::MIN;
        foreach ($items as $item) {
            if ($item->start < $start) {
                $start = $item->start;
            }
            if ($item->end > $end) {
                $end = $item->end;
            }
        }

        return new static($start, $end);
    }

    // A and B
    // A1----B1****A2----B2 -> [B1, A2]
    // A1----A2    B1----B2 -> [MAX, MIN]
    public function intersect(self ...$items): self
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $result = array_shift($items);
        /** @var \Dogma\Math\Range\IntRange $item */
        foreach ($items as $item) {
            if ($result->end >= $item->start) {
                $result = new static(max($result->start, $item->start), min($result->end, $item->end));
            } else {
                return static::createEmpty();
            }
        }

        return $result;
    }

    // A or B
    // A1****B1****A2****B2 -> {[A1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function union(self ...$items): IntRangeSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var \Dogma\Math\Range\IntRange $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($current->end >= $item->start - 1) {
                $current = new static($current->start, max($current->end, $item->end));
                $results[count($results) - 1] = $current;
            } else {
                $current = $item;
                $results[] = $current;
            }
        }

        return new IntRangeSet($results);
    }

    // A xor B
    // A1****B1----A2****B2 -> {[A1, A2], [B1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function difference(self ...$items): IntRangeSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as $i => [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new IntRangeSet($results);
    }

    // A minus B
    // A1****B1----A2----B2 -> {[A1, B1]}
    // A1****A2    B1----B2 -> {[A1, A2]}
    public function subtract(self ...$items): IntRangeSet
    {
        $ranges = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            /** @var \Dogma\Math\Range\IntRange $range */
            foreach ($ranges as $r => $range) {
                unset($ranges[$r]);
                if ($range->start < $item->start && $range->end > $item->end) {
                    $ranges[] = new static($range->start, $item->start - 1);
                    $ranges[] = new static($item->end + 1, $range->end);
                } elseif ($range->start < $item->start) {
                    $ranges[] = new static($range->start, min($range->end, $item->start - 1));
                } elseif ($range->end > $item->end) {
                    $ranges[] = new static(max($range->start, $item->end + 1), $range->end);
                }
            }
        }

        return new IntRangeSet(array_values($ranges));
    }

    // All minus A
    public function invert(): IntRangeSet
    {
        return self::createAll()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Math\Range\IntRange ...$items
     * @return \Dogma\Math\Range\IntRange[][]|int[][] ($ident => ($range, $count))
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Math\Range\IntRange $overlap */
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
     * O(n log n)
     * @param \Dogma\Math\Range\IntRange ...$items
     * @return \Dogma\Math\Range\IntRange[]
     */
    public static function explodeOverlaps(self ...$items): array
    {
        // 0-5 1-6 2-7 -->  0-0 1-1 1-1 2-5 2-5 2-5 6-6 6-6 7-7

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
            /** @var \Dogma\Math\Range\IntRange $b */
            foreach ($items as $j => $b) {
                if ($j < $starts[$i]) {
                    continue;
                } elseif ($i === $j) {
                    continue;
                } elseif ($a->end < $b->start || $a->start > $b->end) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($a->start === $b->start) {
                    if ($a->end > $b->end) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end + 1, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2=b2
                        // a1=b1----a2----b2
                    }
                } elseif ($a->start < $b->start) {
                    if ($a->end === $b->end) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start - 1);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end > $b->end) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start - 1);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end + 1, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start - 1);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end > $b->end) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end);
                        $items[$i] = $new;
                        $items[] = new static($b->end + 1, $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2=b2
                        // b1----a1----a2----b2
                    }
                }
            }
            $i++;
        }

        return array_values(self::sort($items));
    }

    /**
     * @param self[] $ranges
     * @return self[]
     */
    public static function sort(array $ranges): array
    {
        return Arr::sortWith($ranges, function (IntRange $a, IntRange $b) {
            return $a->start <=> $b->start ?: $a->end <=> $b->end;
        });
    }

    /**
     * @param self[] $ranges
     * @return self[]
     */
    public static function sortByStart(array $ranges): array
    {
        return Arr::sortWith($ranges, function (IntRange $a, IntRange $b) {
            return $a->start <=> $b->start;
        });
    }

}
