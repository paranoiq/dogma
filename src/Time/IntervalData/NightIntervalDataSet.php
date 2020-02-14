<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\IntervalData;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Equalable;
use Dogma\Pokeable;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\Interval\NightIntervalSet;
use function array_map;
use function array_merge;
use function array_shift;
use function count;
use function is_array;

class NightIntervalDataSet implements Equalable, Pokeable
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\IntervalData\NightIntervalData[] */
    private $intervals;

    /**
     * @param \Dogma\Time\IntervalData\NightIntervalData[] $intervals
     */
    public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, function (NightIntervalData $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    /**
     * @param \Dogma\Time\Interval\NightIntervalSet $set
     * @param mixed|null $data
     * @return \Dogma\Time\IntervalData\NightIntervalDataSet
     */
    public static function createFromNightIntervalSet(NightIntervalSet $set, $data): self
    {
        $intervals = array_map(function (NightInterval $interval) use ($data) {
            return NightIntervalData::createFromNightInterval($interval, $data);
        }, $set->getIntervals());

        return new static($intervals);
    }

    public function poke(): void
    {
        foreach ($this->intervals as $interval) {
            $interval->poke();
        }
    }

    public function toNightIntervalSet(): NightIntervalSet
    {
        $intervals = [];
        /** @var \Dogma\Time\IntervalData\NightIntervalData $interval */
        foreach ($this->intervals as $interval) {
            $intervals[] = $interval->toNightInterval();
        }

        return new NightIntervalSet($intervals);
    }

    /**
     * @return \Dogma\Time\Date[][]|mixed[][] array of pairs: (Date $date, Equalable $data)
     */
    public function toDateDataArray(): array
    {
        $intervals = $this->normalize()->getIntervals();

        return array_merge(...array_map(function (NightIntervalData $interval) {
            return $interval->toDateDataArray();
        }, $intervals));
    }

    /**
     * @return \Dogma\Time\IntervalData\NightIntervalData[]
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
        Check::instance($other, self::class);

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

    public function containsValue(Date $value): bool
    {
        foreach ($this->intervals as $interval) {
            if ($interval->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Join overlapping intervals in set, if they have the same data.
     * @return self
     */
    public function normalize(): self
    {
        $intervals = NightIntervalData::sortByStart($this->intervals);
        $count = count($intervals) - 1;
        for ($n = 0; $n < $count; $n++) {
            $first = $intervals[$n];
            $second = $intervals[$n + 1];
            if ($first->dataEquals($second->getData()) && ($first->intersects($second) || $first->touches($second))) {
                $intervals[$n + 1] = new NightIntervalData(
                    Date::min($first->getStart(), $second->getStart()),
                    Date::max($first->getEnd(), $second->getEnd()),
                    $first->getData()
                );
                unset($intervals[$n]);
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
        return $this->addIntervals(...$set->intervals);
    }

    public function addIntervals(NightIntervalData ...$intervals): self
    {
        return new static(array_merge($this->intervals, $intervals));
    }

    /**
     * Remove another set of intervals from this one.
     * @param \Dogma\Time\Interval\NightIntervalSet $set
     * @return self
     */
    public function subtract(NightIntervalSet $set): self
    {
        return $this->subtractIntervals(...$set->getIntervals());
    }

    public function subtractIntervals(NightInterval ...$intervals): self
    {
        $sources = $this->intervals;
        $results = [];
        /** @var \Dogma\Time\IntervalData\NightIntervalData $result */
        while ($result = array_shift($sources)) {
            foreach ($intervals as $interval) {
                $result = $result->subtract($interval);
                if (count($result->intervals) === 0) {
                    continue 2;
                } elseif (count($result->intervals) === 2) {
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
     * @param \Dogma\Time\Interval\NightIntervalSet $set
     * @return self
     */
    public function intersect(NightIntervalSet $set): self
    {
        return $this->intersectIntervals(...$set->getIntervals());
    }

    public function intersectIntervals(NightInterval ...$intervals): self
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

    public function map(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof NightIntervalData) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } else {
                throw new ShouldNotHappenException('Expected NightIntervalData or NightIntervalDataSet or array of NightIntervalData.');
            }
        }

        return new static($results);
    }

    public function collect(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof NightIntervalData) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } elseif ($result === null) {
                continue;
            } else {
                throw new ShouldNotHappenException('Expected NightIntervalData or NightIntervalDataSet or array of NightIntervalData.');
            }
        }

        return new static($results);
    }

    public function collectData(callable $mapper): self
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $resultData = $mapper($interval->getData());
            if ($resultData !== null) {
                $results[] = new NightIntervalData($interval->getStart(), $interval->getEnd(), $resultData);
            }
        }

        return new static($results);
    }

    /**
     * Apply other DateDataIntervalSet on this one with reduce function. Only modifies and splits intersecting intervals. Does not insert new ones.
     * @param self $other
     * @param callable $reducer
     * @return self
     */
    public function modifyData(self $other, callable $reducer): self
    {
        $results = $this->getIntervals();
        /** @var \Dogma\Time\IntervalData\NightIntervalData $interval */
        foreach ($other->getIntervals() as $interval) {
            /** @var \Dogma\Time\IntervalData\NightIntervalData $result */
            foreach ($results as $i => $result) {
                if (!$result->intersects($interval)) {
                    continue;
                }
                $newData = $reducer($result->getData(), $interval->getData());
                if ($result->dataEquals($newData)) {
                    continue;
                }
                if ($interval->contains($result)) {
                    // i1----r1****r2----i2
                    $results[$i] = new NightIntervalData($result->getStart(), $result->getEnd(), $newData);
                } elseif ($interval->getStart()->isAfter($result->getStart()) && $interval->getEnd()->isBefore($result->getEnd())) {
                    // r1====i1****i2====r1
                    $results[$i] = new NightIntervalData($interval->getStart(), $interval->getEnd(), $newData);
                    $results[] = new NightIntervalData($result->getStart(), $interval->getStart(), $result->getData());
                    $results[] = new NightIntervalData($interval->getEnd(), $result->getEnd(), $result->getData());
                } elseif ($interval->getStart()->isAfter($result->getStart())) {
                    // r1====i1****r2----i2
                    $results[$i] = new NightIntervalData($result->getStart(), $interval->getStart(), $result->getData());
                    $results[] = new NightIntervalData($interval->getStart(), $result->getEnd(), $newData);
                } elseif ($interval->getEnd()->isBefore($result->getEnd())) {
                    // i1----r1****i2====r2
                    $results[] = new NightIntervalData($result->getStart(), $interval->getEnd(), $newData);
                    $results[$i] = new NightIntervalData($interval->getEnd(), $result->getEnd(), $result->getData());
                }
            }
        }

        return new static($results);
    }

    /**
     * Split interval set to more interval sets with different subsets of original data.
     * @param callable $splitter Maps original data set to a group of data sets. Should return array with keys indicating the data set group.
     * @return self[] $this
     */
    public function splitData(callable $splitter): array
    {
        $intervalGroups = [];
        foreach ($this->intervals as $interval) {
            foreach ($splitter($interval->getData()) as $key => $values) {
                $intervalGroups[$key][] = new NightIntervalData($interval->getStart(), $interval->getEnd(), $values);
            }
        }

        $intervalSets = [];
        foreach ($intervalGroups as $intervals) {
            $intervalSets[] = (new NightIntervalDataSet($intervals))->normalize();
        }

        return $intervalSets;
    }

}
