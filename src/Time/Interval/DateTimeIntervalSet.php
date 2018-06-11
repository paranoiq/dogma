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

    public function contains(DateTime $value): bool
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

}
