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
use Dogma\Time\Time;

class TimeIntervalSet implements DateOrTimeIntervalSet
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\Interval\TimeInterval[] */
    private $intervals;

    /**
     * @param \Dogma\Time\Interval\TimeInterval[] $intervals
     */
    public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, function (TimeInterval $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    public function format(string $format = TimeInterval::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        return implode(', ', Arr::map($this->intervals, function (TimeInterval $timeInterval) use ($format, $formatter): string {
            return $timeInterval->format($format, $formatter);
        }));
    }

    /**
     * @return \Dogma\Time\Interval\TimeInterval[]
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

    public function contains(Time $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): TimeInterval
    {
        if ($this->intervals === []) {
            return TimeInterval::empty();
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
        ///
        return new self([]);
    }

    /**
     * Add another set of intervals to this one without normalisation.
     * @param self $set
     * @return self
     */
    public function add(self $set): self
    {
        ///
        return new self([]);
    }

    /**
     * Remove another set of intervals from this one.
     * @param self $set
     * @return self
     */
    public function subtract(self $set): self
    {
        ///
        return new self([]);
    }

    /**
     * Intersect with another set of intervals.
     * @param self $set
     * @return self
     */
    public function intersect(self $set): self
    {
        ///
        return new self([]);
    }

}
