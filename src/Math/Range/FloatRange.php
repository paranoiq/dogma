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
use Dogma\InvalidValueException;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;

class FloatRange
{
    use StrictBehaviorMixin;

    public const MIN = -INF;
    public const MAX = INF;

    public const EXCLUSIVE = true;
    public const INCLUSIVE = false;

    public const SPLIT_EXCLUSIVE_STARTS = 1;
    public const SPLIT_SHARED_STARTS_ENDS = 0;
    public const SPLIT_EXCLUSIVE_ENDS = -1;

    /** @var float */
    private $start;

    /** @var float */
    private $end;

    /** @var bool */
    private $startExclusive;

    /** @var bool */
    private $endExclusive;

    public function __construct(float $start, float $end, bool $startExclusive = false, bool $endExclusive = false)
    {
        if (is_nan($start)) {
            throw new InvalidValueException($start, Type::FLOAT);
        }
        if (is_nan($end)) {
            throw new InvalidValueException($end, Type::FLOAT);
        }
        Check::min($end, $start);

        $this->start = $start;
        $this->end = $end;
        $this->startExclusive = $startExclusive;
        $this->endExclusive = $endExclusive;

        if ($start === $end) {
            if ($startExclusive || $endExclusive) {
                // default createEmpty()
                $this->start = self::MAX;
                $this->end = self::MIN;
                $this->startExclusive = $this->endExclusive = false;
            }
        }
    }

    public static function createEmpty(): self
    {
        $range = new static(0.0, 0.0);
        $range->start = self::MAX;
        $range->end = self::MIN;

        return $range;
    }

    public static function createAll(): self
    {
        return new static(self::MIN, self::MAX);
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(float $byValue): self
    {
        return new static($this->start + $byValue, $this->end + $byValue, $this->startExclusive, $this->endExclusive);
    }

    public function multiply(float $byValue): self
    {
        return new static($this->start * $byValue, $this->end * $byValue, $this->startExclusive, $this->endExclusive);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(?int $decimals = 15, string $decimalPoint = '.'): string
    {
        return sprintf(
            '%s%s, %s%s',
            $this->startExclusive ? '(' : '[',
            number_format($this->start, $decimals, $decimalPoint, ''),
            number_format($this->end, $decimals, $decimalPoint, ''),
            $this->endExclusive ? ')' : ']'
        );
    }

    public function getStart(): float
    {
        return $this->start;
    }

    public function getEnd(): float
    {
        return $this->end;
    }

    public function isStartExclusive(): bool
    {
        return $this->startExclusive;
    }

    public function isEndExclusive(): bool
    {
        return $this->endExclusive;
    }

    public function isEmpty(): bool
    {
        return $this->start > $this->end || ($this->start === $this->end && $this->startExclusive && $this->endExclusive);
    }

    public function equals(self $range): bool
    {
        return ($this->start === $range->start
            && $this->end === $range->end
            && $this->startExclusive === $range->startExclusive
            && $this->endExclusive === $range->endExclusive)
            || ($this->isEmpty() && $range->isEmpty());
    }

    public function containsValue(float $value): bool
    {
        return ($this->startExclusive ? $value > $this->start : $value >= $this->start)
            && ($this->endExclusive ? $value < $this->end : $value <= $this->end);
    }

    public function contains(self $range): bool
    {
        return !$range->isEmpty()
            && (($this->startExclusive && !$range->startExclusive) ? $range->start > $this->start : $range->start >= $this->start)
            && (($this->endExclusive && !$range->endExclusive) ? $range->end < $this->end : $range->end <= $this->end);
    }

    public function intersects(self $range): bool
    {
        return $this->containsValue($range->start) || $this->containsValue($range->end) || $range->containsValue($this->start) || $range->containsValue($this->end);
    }

    public function touches(self $range, bool $exclusive = false): bool
    {
        return ($this->start === $range->end && ($exclusive ? ($this->startExclusive xor $range->endExclusive) : true))
            || ($this->end === $range->start && ($exclusive ? ($this->endExclusive xor $range->startExclusive) : true));
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts, int $splitMode = self::SPLIT_SHARED_STARTS_ENDS): FloatRangeSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new FloatRangeSet([$this]);
        }

        $partSize = ($this->end - $this->start) / $parts;
        $borders = [];
        for ($n = 1; $n < $parts; $n++) {
            $borders[] = $this->start + $partSize * $n;
        }
        $borders = array_unique($borders);

        return $this->splitBy($borders, $splitMode);
    }

    /**
     * @param float[] $rangeStarts
     * @param int $splitMode
     * @return \Dogma\Math\Range\FloatRangeSet
     */
    public function splitBy(array $rangeStarts, int $splitMode = self::SPLIT_SHARED_STARTS_ENDS): FloatRangeSet
    {
        $rangeStarts = Arr::sort($rangeStarts);
        $results = [$this];
        $i = 0;
        foreach ($rangeStarts as $rangeStart) {
            /** @var \Dogma\Math\Range\FloatRange $range */
            $range = $results[$i];
            if ($range->containsValue($rangeStart)) {
                $results[$i] = new static($range->start, $rangeStart, $range->startExclusive, $splitMode === self::SPLIT_EXCLUSIVE_ENDS ? self::EXCLUSIVE : self::INCLUSIVE);
                $results[] = new static($rangeStart, $range->end, $splitMode === self::SPLIT_EXCLUSIVE_STARTS ? self::EXCLUSIVE : self::INCLUSIVE, $range->endExclusive);
                $i++;
            }
        }

        return new FloatRangeSet($results);
    }

    // A1****A2****B1****B2 -> [A1, B2]
    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = self::MAX;
        $end = self::MIN;
        $startExclusive = true;
        $endExclusive = true;
        foreach ($items as $item) {
            if ($item->start < $start) {
                $start = $item->start;
                $startExclusive = $item->startExclusive;
            } elseif ($startExclusive && !$item->startExclusive && $item->start === $start) {
                $startExclusive = false;
            }
            if ($item->end > $end) {
                $end = $item->end;
                $endExclusive = $item->endExclusive;
            } elseif ($endExclusive && !$item->endExclusive && $item->end === $end) {
                $endExclusive = false;
            }
        }

        return new static($start, $end, $startExclusive, $endExclusive);
    }

    // A1----B1****A2----B2 -> [B1, A2]
    // A1----A2    B1----B2 -> [MAX, MIN]
    public function intersect(self ...$items): self
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $result = array_shift($items);
        /** @var \Dogma\Math\Range\FloatRange $item */
        foreach ($items as $item) {
            if ($result->start < $item->start || ($result->start === $item->start && $result->startExclusive && !$item->startExclusive)) {
                if ($result->end < $item->start || ($result->end === $item->start && ($result->endExclusive || $item->startExclusive))) {
                    return self::createEmpty();
                }
                $result = new static(
                    $item->start,
                    $result->end,
                    $item->startExclusive,
                    $result->endExclusive
                );
            }
            if ($result->end > $item->end || ($result->end === $item->end && !$result->endExclusive && $item->endExclusive)) {
                if ($result->start > $item->end || ($result->start === $item->end && ($result->startExclusive || $item->endExclusive))) {
                    return self::createEmpty();
                }
                $result = new static(
                    $result->start,
                    $item->end,
                    $result->startExclusive,
                    $item->endExclusive
                );
            }
        }

        return $result;
    }

    // A1****B1****A2****B2 -> {[A1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function union(self ...$items): FloatRangeSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var \Dogma\Math\Range\FloatRange $item */
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

        return new FloatRangeSet($results);
    }

    // A xor B
    // A1****B1----A2****B2 -> {[A1, A2], [B1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    public function difference(self ...$items): FloatRangeSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as $i => [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new FloatRangeSet($results);
    }

    // A minus B
    // A1****B1----A2----B2 -> {[A1, B1]}
    // A1****A2    B1----B2 -> {[A1, A2]}
    public function subtract(self ...$items): FloatRangeSet
    {
        $results = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            /** @var \Dogma\Math\Range\FloatRange $range */
            foreach ($results as $r => $range) {
                $startLower = $range->start < $item->start || ($range->start === $item->start && !$range->startExclusive && $item->startExclusive);
                $endHigher = $range->end > $item->end || ($range->end === $item->end && $range->endExclusive && !$item->endExclusive);
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($range->start, $item->start, $range->startExclusive, !$item->startExclusive);
                    $results[] = new static($item->end, $range->end, !$item->endExclusive, $range->endExclusive);
                } elseif ($startLower) {
                    if ($range->end < $item->start || ($range->end === $item->start && $range->endExclusive && !$item->startExclusive)) {
                        // r1****r2    i1----i2
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($range->start, $item->start, $range->startExclusive, !$item->startExclusive);
                    }
                } elseif ($endHigher) {
                    if ($range->start > $item->end || ($range->start === $item->end && $range->startExclusive && !$item->endExclusive)) {
                        // i1----i2    r1****r2
                    } else {
                        // i1----r1----i2****r2
                        unset($results[$r]);
                        $results[] = new static($item->end, $range->end, !$item->endExclusive, $range->endExclusive);
                    }
                } else {
                    // i1----r1----r2----i2
                    unset($results[$r]);
                }
            }
        }

        return new FloatRangeSet(array_values($results));
    }

    // All minus A
    public function invert(): FloatRangeSet
    {
        return self::createAll()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Math\Range\FloatRange ...$items
     * @return \Dogma\Math\Range\FloatRange[][]|int[][] ($ident => ($range, $count))
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Math\Range\FloatRange $overlap */
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
     * @param \Dogma\Math\Range\FloatRange ...$items
     * @return \Dogma\Math\Range\FloatRange[]
     */
    public static function explodeOverlaps(self ...$items): array
    {
        // 0-5 1-6 2-7 -->  0-1< 1-2< 1-2< 2-5 2-5 2-5 >5-6 >5-6 >6-7

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
            /** @var \Dogma\Math\Range\FloatRange $b */
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    continue;
                } elseif ($j < $starts[$i]) {
                    continue;
                } elseif ($a->end < $b->start || ($a->end === $b->start && ($a->endExclusive || $b->startExclusive))
                    || $a->start > $b->end || ($a->start === $b->end && ($a->startExclusive || $b->endExclusive))) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($a->start === $b->start && $a->startExclusive === $b->startExclusive) {
                    if ($a->end === $b->end && $a->endExclusive === $b->endExclusive) {
                        // a1=b1----a2=b2
                    } elseif ($a->end > $b->end || ($a->end === $b->end && $a->endExclusive === false)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end, $a->end, !$b->endExclusive, $a->endExclusive);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2----b2
                    }
                } elseif ($a->start < $b->start || ($a->start === $b->start && $a->startExclusive === false)) {
                    if ($a->end === $b->end && $a->endExclusive === $b->endExclusive) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->startExclusive, !$b->startExclusive);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end > $b->end || ($a->end === $b->end && $a->endExclusive === false)) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start, $a->startExclusive, !$b->startExclusive);
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end, $a->end, !$b->endExclusive, $a->endExclusive);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end, $b->startExclusive, $a->endExclusive);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start, $a->startExclusive, !$b->startExclusive);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end === $b->end && $a->endExclusive === $b->endExclusive) {
                        // b1----a1----a2=b2
                    } elseif ($a->end > $b->end || ($a->end === $b->end && $a->endExclusive === false)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end, $a->startExclusive, $b->endExclusive);
                        $items[$i] = $new;
                        $items[] = new static($b->end, $a->end, !$b->endExclusive, $a->startExclusive);
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
     * @param self[] $ranges
     * @return self[]
     */
    public static function sort(array $ranges): array
    {
        return Arr::sortWith($ranges, function (FloatRange $a, FloatRange $b) {
            return $a->start <=> $b->start ?: !$b->startExclusive <=> !$a->startExclusive
                ?: $a->end <=> $b->end ?: !$b->endExclusive <=> !$a->endExclusive;
        });
    }

    /**
     * @param self[] $ranges
     * @return self[]
     */
    public static function sortByStart(array $ranges): array
    {
        return Arr::sortWith($ranges, function (FloatRange $a, FloatRange $b) {
            return $a->start <=> $b->start ?: $b->startExclusive <=> $a->startExclusive;
        });
    }

}
