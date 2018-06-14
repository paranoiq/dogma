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
use Dogma\Equalable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;

class DateTimeIntervalSet implements DateOrTimeIntervalSet
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\Interval\DateTimeInterval[] */
    private $intervals;

    /**
     * @param \Dogma\Time\Interval\DateTimeInterval[] $intervals
     */
    public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, function (DateTimeInterval $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    public function format(string $format = DateTimeInterval::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        return implode(', ', Arr::map($this->intervals, function (DateTimeInterval $dateTimeInterval) use ($format, $formatter): string {
            return $dateTimeInterval->format($format, $formatter);
        }));
    }

    /**
     * @return \Dogma\Time\Interval\DateTimeInterval[]
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

    public function containsValue(DateTime $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): DateTimeInterval
    {
        if ($this->intervals === []) {
            return DateTimeInterval::empty();
        } else {
            return reset($this->intervals)->envelope(...$this->intervals);
        }
    }

    /**
     * Join overlapping intervals in set.
     * @return self
     */
    public function normalize(): self
    {
        $intervals = DateTimeInterval::sortByStart($this->intervals);
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
     * Add another set of intervals to this one without normalisation.
     * @param self $set
     * @return self
     */
    public function add(self $set): self
    {
        return self::addIntervals(...$set->intervals);
    }

    public function addIntervals(DateTimeInterval ...$intervals): self
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

    public function subtractIntervals(DateTimeInterval ...$intervals): self
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

    public function intersectIntervals(DateTimeInterval ...$intervals): self
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

}
