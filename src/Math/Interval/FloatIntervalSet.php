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
use Dogma\Equalable;
use Dogma\StrictBehaviorMixin;

class FloatIntervalSet implements IntervalSet
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Math\Interval\FloatInterval[] */
    private $intervals;

    /**
     * @param \Dogma\Math\Interval\FloatInterval[] $intervals
     */
    public function __construct(array $intervals)
    {
        Check::itemsOfType($intervals, FloatInterval::class);

        $this->intervals = $intervals;
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

}
