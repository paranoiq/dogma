<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Check;
use Dogma\Compare;
use Dogma\Equalable;
use Dogma\StrictBehaviorMixin;
use function array_merge;
use function array_shift;
use function array_values;
use function count;
use function end;

class FloatIntervalSet implements IntervalSet
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Math\Interval\FloatInterval[] */
    private $intervals = [];

    /**
     * @param \Dogma\Math\Interval\FloatInterval[] $intervals
     */
    public function __construct(array $intervals)
    {
        Check::itemsOfType($intervals, FloatInterval::class);

        foreach ($intervals as $interval) {
            if (!$interval->isEmpty()) {
                $this->intervals[] = $interval;
            }
        }
    }

    /**
     * @return \Dogma\Math\Interval\FloatInterval[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    public function isEmpty(): bool
    {
        return $this->intervals === [];
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        $other instanceof self || Check::object($other, self::class);

        $otherIntervals = $other->getIntervals();
        if (count($this->intervals) !== count($otherIntervals)) {
            return false;
        }
        foreach ($this->intervals as $i => $interval) {
            if (!$interval->equals($otherIntervals[$i])) {
                return false;
            }
        }

        return true;
    }

    public function containsValue(float $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): FloatInterval
    {
        if ($this->intervals === []) {
            return FloatInterval::empty();
        } else {
            return end($this->intervals)->envelope(...$this->intervals);
        }
    }

    /**
     * Join overlapping intervals in set.
     * @return self
     */
    public function normalize(): self
    {
        $intervals = FloatInterval::sortByStart($this->intervals);
        for ($n = 0; $n < count($intervals) - 1; $n++) {
            if ($intervals[$n]->intersects($intervals[$n + 1])) {
                $intervals[$n] = $intervals[$n]->envelope($intervals[$n + 1]);
                unset($intervals[$n + 1]);
                $intervals = array_values($intervals);
            }
        }

        return new static($intervals);
    }

    /**
     * Add another set of intervals to this one without normalization.
     * @param self $set
     * @return self
     */
    public function add(self $set): self
    {
        return self::addIntervals(...$set->intervals);
    }

    public function addIntervals(FloatInterval ...$intervals): self
    {
        return new static(array_merge($this->intervals, $intervals));
    }

    /**
     * Remove another set of intervals from this one.
     * @param self $set
     * @return self
     */
    public function subtract(self $set): self
    {
        return self::subtractIntervals(...$set->intervals);
    }

    public function subtractIntervals(FloatInterval ...$intervals): self
    {
        $sources = $this->intervals;
        $results = [];
        while ($result = array_shift($sources)) {
            foreach ($intervals as $interval) {
                $result = $result->subtract($interval);
                if (count($result->intervals) === 2) {
                    $sources[] = $result->intervals[1];
                }
                $result = $result->intervals[0];
            }
            if (!$result->isEmpty()) {
                $results[] = $result;
            }
        }

        return new static($results);
    }

    /**
     * Intersect with another set of intervals.
     * @param self $set
     * @return self
     */
    public function intersect(self $set): self
    {
        return self::intersectIntervals(...$set->intervals);
    }

    public function intersectIntervals(FloatInterval ...$intervals): self
    {
        $results = [];
        foreach ($this->intervals as $result) {
            foreach ($intervals as $interval) {
                if ($result->intersects($interval)) {
                    $results[] = $result->intersect($interval);
                }
            }
        }

        return new static($results);
    }

    public function filterByLength(string $operator, float $length): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $interval->getLength() <=> $length;
            switch ($operator) {
                case Compare::LESSER:
                    if ($result === -1) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::LESSER_OR_EQUAL:
                    if ($result !== 1) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::EQUAL:
                    if ($result === 0) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::NOT_EQUAL:
                    if ($result !== 0) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::GREATER_OR_EQUAL:
                    if ($result !== -1) {
                        $results[] = $interval;
                    }
                    break;
                case Compare::GREATER:
                    if ($result === 1) {
                        $results[] = $interval;
                    }
                    break;
            }
        }

        return new static($results);
    }

    public function map(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result !== null) {
                $results[] = $result;
            }
        }

        return new static($results);
    }

}
