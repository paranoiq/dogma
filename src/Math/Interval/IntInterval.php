<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\StrictBehaviorMixin;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function max;
use function min;
use function round;
use function sprintf;
use function usort;

class IntInterval implements Interval
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

    public static function empty(): self
    {
        $interval = new static(0, 0);
        $interval->start = self::MAX;
        $interval->end = self::MIN;

        return $interval;
    }

    public static function all(): self
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

    public function getLength(): int
    {
        return $this->start > $this->end ? 0 : $this->end - $this->start;
    }

    public function getCount(): int
    {
        return $this->start > $this->end ? 0 : $this->end - $this->start + 1;
    }

    public function isEmpty(): bool
    {
        return $this->start > $this->end;
    }

    /**
     * @param \Dogma\Math\Interval\IntInterval $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->start === $other->start && $this->end === $other->end;
    }

    /**
     * @param \Dogma\Math\Interval\IntInterval $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start <=> $other->start ?: $this->end <=> $other->end;
    }

    public function containsValue(int $value): bool
    {
        return $value >= $this->start && $value <= $this->end;
    }

    public function contains(self $interval): bool
    {
        return $this->start <= $interval->start && $this->end >= $interval->end && !$interval->isEmpty();
    }

    public function intersects(self $interval): bool
    {
        return $this->start <= $interval->end && $this->end >= $interval->start;
    }

    public function touches(self $interval): bool
    {
        return $this->start === $interval->end + 1 || $this->end === $interval->start - 1;
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): IntIntervalSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new IntIntervalSet([$this]);
        }

        $partSize = ($this->end - $this->start + 1) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            $intervalStarts[] = (int) round($this->start + $partSize * $n);
        }
        $intervalStarts = array_unique($intervalStarts);

        if ($intervalStarts === []) {
            return new IntIntervalSet([$this]);
        }

        return $this->splitBy($intervalStarts);
    }

    /**
     * @param int[] $intervalStarts
     * @return \Dogma\Math\Interval\IntIntervalSet
     */
    public function splitBy(array $intervalStarts): IntIntervalSet
    {
        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        foreach ($intervalStarts as $intervalStart) {
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart) && $interval->containsValue($intervalStart - 1)) {
                $results[$i] = new static($interval->start, $intervalStart - 1);
                $results[] = new static($intervalStart, $interval->end);
                $i++;
            }
        }

        return new IntIntervalSet($results);
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
        /** @var \Dogma\Math\Interval\IntInterval $item */
        foreach ($items as $item) {
            if ($result->end >= $item->start) {
                $result = new static(max($result->start, $item->start), min($result->end, $item->end));
            } else {
                return static::empty();
            }
        }

        return $result;
    }

    // A or B
    // A1****B1****A2****B2 -> {[A1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function union(self ...$items): IntIntervalSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var \Dogma\Math\Interval\IntInterval $item */
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

        return new IntIntervalSet($results);
    }

    // A xor B
    // A1****B1----A2****B2 -> {[A1, A2], [B1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function difference(self ...$items): IntIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new IntIntervalSet($results);
    }

    // A minus B
    // A1****B1----A2----B2 -> {[A1, B1]}
    // A1****A2    B1----B2 -> {[A1, A2]}
    public function subtract(self ...$items): IntIntervalSet
    {
        $intervals = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            /** @var \Dogma\Math\Interval\IntInterval $interval */
            foreach ($intervals as $r => $interval) {
                unset($intervals[$r]);
                if ($interval->start < $item->start && $interval->end > $item->end) {
                    $intervals[] = new static($interval->start, $item->start - 1);
                    $intervals[] = new static($item->end + 1, $interval->end);
                } elseif ($interval->start < $item->start) {
                    $intervals[] = new static($interval->start, min($interval->end, $item->start - 1));
                } elseif ($interval->end > $item->end) {
                    $intervals[] = new static(max($interval->start, $item->end + 1), $interval->end);
                }
            }
        }

        return new IntIntervalSet(array_values($intervals));
    }

    // All minus A
    public function invert(): IntIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Math\Interval\IntInterval ...$items
     * @return \Dogma\Math\Interval\IntInterval[][]|int[][] ($ident => ($interval, $count))
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Math\Interval\IntInterval $overlap */
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
     * @param \Dogma\Math\Interval\IntInterval ...$items
     * @return \Dogma\Math\Interval\IntInterval[]
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
            /** @var \Dogma\Math\Interval\IntInterval $b */
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
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
                        continue;
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
        usort($intervals, function (IntInterval $a, IntInterval $b) {
            return $a->start <=> $b->start ?: $a->end <=> $b->end;
        });

        return $intervals;
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        usort($intervals, function (IntInterval $a, IntInterval $b) {
            return $a->start <=> $b->start;
        });

        return $intervals;
    }

}
