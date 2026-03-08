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
use Dogma\ArrayIterator;
use Dogma\Check;
use Dogma\Equalable;
use Dogma\IntersectResult;
use Dogma\Math\Interval\IntervalCalc;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\Interval\NightIntervalSet;
use IteratorAggregate;
use Traversable;
use function array_map;
use function array_merge;
use function array_shift;
use function array_splice;
use function count;
use function is_array;

/**
 * @template TData
 * @implements IteratorAggregate<NightIntervalData<TData>>
 */
class NightIntervalDataSet implements Equalable, IteratorAggregate
{
    use StrictBehaviorMixin;

    /** @var array<NightIntervalData<TData>> */
    private array $intervals;

    /**
     * @param array<NightIntervalData<TData>> $intervals
     */
    final public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, static function (NightIntervalData $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    /**
     * @param TData $data
     * @return NightIntervalDataSet<TData>
     */
    public static function createFromNightIntervalSet(NightIntervalSet $set, mixed $data): self
    {
        $intervals = array_map(static function (NightInterval $interval) use ($data) {
            return NightIntervalData::createFromNightInterval($interval, $data);
        }, $set->getIntervals());

        return new static($intervals);
    }

    public function toNightIntervalSet(): NightIntervalSet
    {
        $intervals = [];
        /** @var NightIntervalData<TData> $interval */
        foreach ($this->intervals as $interval) {
            $intervals[] = $interval->toNightInterval();
        }

        return new NightIntervalSet($intervals);
    }

    /**
     * @return array<array{Date, TData}>
     */
    public function toDateDataArray(): array
    {
        $intervals = $this->normalize()->getIntervals();

        return array_merge(...array_map(static function (NightIntervalData $interval) {
            return $interval->toDateDataArray();
        }, $intervals));
    }

    /**
     * @return array<NightIntervalData<TData>>
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    /**
     * @return Traversable<NightIntervalData<TData>>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->intervals);
    }

    public function isEmpty(): bool
    {
        return $this->intervals === [];
    }

    /**
     * @param self<TData> $other
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
     * @return static<TData>
     */
    public function normalize(): static
    {
        /** @var array<NightIntervalData<TData>> $intervals */
        $intervals = Arr::sortComparableValues($this->intervals);
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
     * @param self<TData> $set
     * @return static<TData>
     */
    public function add(self $set): static
    {
        return $this->addIntervals(...$set->intervals); // @phpstan-ignore return.type (should return static[Dogma\Time\IntervalData\NightIntervalDataSet<TData>] but returns static[Dogma\Time\IntervalData\NightIntervalDataSet<TData>] - WTAF?!)
    }

    /**
     * @param NightIntervalData<TData> ...$intervals
     */
    public function addIntervals(NightIntervalData ...$intervals): static
    {
        /** @var list<NightIntervalData<TData>> $merge */
        $merge = array_merge($this->intervals, $intervals);

        return new static($merge);
    }

    /**
     * Remove another set of intervals from this one.
     * @return static<TData>
     */
    public function subtract(NightIntervalSet $set): static
    {
        return $this->subtractIntervals(...$set->getIntervals()); // @phpstan-ignore return.type (should return static[Dogma\Time\IntervalData\NightIntervalDataSet<TData>] but returns static[Dogma\Time\IntervalData\NightIntervalDataSet<TData>] - WTAF?!)
    }

    public function subtractIntervals(NightInterval ...$intervals): static
    {
        $sources = $this->intervals;
        $results = [];
        /** @var NightIntervalData<TData> $result */
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

        /** @var array<NightIntervalData<TData>> $results */
        $results = $results;

        return new static($results);
    }

    /**
     * Intersect with another set of intervals.
     * @return static<TData>
     */
    public function intersect(NightIntervalSet $set): static
    {
        return $this->intersectIntervals(...$set->getIntervals()); // @phpstan-ignore return.type (should return static[Dogma\Time\IntervalData\NightIntervalDataSet<TData>] but returns static[Dogma\Time\IntervalData\NightIntervalDataSet<TData>] - WTAF?!)
    }

    public function intersectIntervals(NightInterval ...$intervals): static
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

    /**
     * @template TNewData
     * @param callable(NightIntervalData<TData> $data): (self<TNewData>|NightIntervalData<TNewData>|array<NightIntervalData<TNewData>>) $mapper
     * @return static<TNewData>
     */
    public function map(callable $mapper): static
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

    /**
     * @template TNewData
     * @param callable(NightIntervalData<TData> $data): (self<TNewData>|NightIntervalData<TNewData>|array<NightIntervalData<TNewData>>|null) $mapper
     * @return static<TNewData>
     */
    public function collect(callable $mapper): static
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

    /**
     * @template TNewData
     * @param callable(TData $data):(TNewData|null) $mapper
     * @return static<TNewData>
     */
    public function collectData(callable $mapper): static
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $resultData = $mapper($interval->getData());
            if ($resultData !== null) {
                $results[] = new NightIntervalData($interval->getStart(), $interval->getEnd(), $resultData);
            }
        }

        return new static($results); // @phpstan-ignore return.type (should return ..NightIntervalDataSet<TNewData> but returns ..NightIntervalDataSet<TNewData of mixed> - WTAF?!)
    }

    /**
     * Apply another NightIntervalDataSet on this one with reduce function.
     * Only modifies and splits intersecting intervals. Does not insert new ones nor remove things.
     * Complexity O(m*n). For bigger sets use modifyDataByStream()
     *
     * @template TOther
     * @param self<TOther> $other
     * @param callable(TData, TOther): TData $reducer
     * @return static<TData>
     */
    public function modifyData(self $other, callable $reducer): static
    {
        $results = $this->getIntervals();
        foreach ($other->getIntervals() as $interval) {
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
     * Apply inputs (mappable to start and end dates) to this data set with reduce function.
     * Only modifies and splits intersecting intervals. Does not insert new ones nor remove things.
     * Both $this and inputs must be ordered to work properly, $this must be normalized.
     * Complexity ~O(m+n), worst case O(m*n) if all inputs cover whole interval set.
     *
     * @template TInput
     * @param iterable<TInput> $inputs
     * @param callable(TInput): array{Date, Date} $mapper
     * @param callable(TData, TInput): TData $reducer
     * @return static<TData>
     */
    public function modifyDataByStream(iterable $inputs, callable $mapper, callable $reducer): static
    {
        $results = $this->getIntervals();
        $resultsCount = count($results);
        $startIndex = 0;
        foreach ($inputs as $input) {
            $currentIndex = $startIndex;
            /** @var Date $inputStart */
            /** @var Date $inputEnd */
            [$inputStart, $inputEnd] = $mapper($input);
            while ($currentIndex < $resultsCount) {
                $result = $results[$currentIndex];
                [$resultStart, $resultEnd] = $result->getStartEnd();

                $intersect = IntervalCalc::compareIntersects(
                    $inputStart->getJulianDay(),
                    $inputEnd->getJulianDay() - 1,
                    $resultStart->getJulianDay(),
                    $resultEnd->getJulianDay() - 1
                );
                switch ($intersect) {
                    case IntersectResult::BEFORE_START:
                    case IntersectResult::TOUCHES_START:
                        // skip input
                        continue 3;
                    case IntersectResult::AFTER_END:
                    case IntersectResult::TOUCHES_END:
                        // next result
                        $currentIndex++;
                        continue 2;
                }

                $oldData = $result->getData();
                $newData = $reducer($oldData, $input);
                if ($result->dataEquals($newData)) {
                    $currentIndex++;
                    continue;
                }

                switch ($intersect) {
                    case IntersectResult::INTERSECTS_START:
                    case IntersectResult::FITS_TO_START:
                        array_splice($results, $currentIndex, 1, [
                            new NightIntervalData($resultStart, $inputEnd, $newData),
                            new NightIntervalData($inputEnd, $resultEnd, $oldData),
                        ]);
                        $resultsCount++;
                        continue 3; // next input
                    case IntersectResult::FITS_TO_END:
                        array_splice($results, $currentIndex, 1, [
                            new NightIntervalData($resultStart, $inputStart, $oldData),
                            new NightIntervalData($inputStart, $resultEnd, $newData),
                        ]);
                        $resultsCount++;
                        continue 3; // next input
                    case IntersectResult::INTERSECTS_END:
                        array_splice($results, $currentIndex, 1, [
                            new NightIntervalData($resultStart, $inputStart, $oldData),
                            new NightIntervalData($inputStart, $resultEnd, $newData),
                        ]);
                        $resultsCount++;
                        $currentIndex += 2;
                        break;
                    case IntersectResult::EXTENDS_START:
                    case IntersectResult::SAME:
                        $results[$currentIndex] = new NightIntervalData($resultStart, $resultEnd, $newData);
                        continue 3; // next input
                    case IntersectResult::EXTENDS_END:
                    case IntersectResult::CONTAINS:
                        $results[$currentIndex] = new NightIntervalData($resultStart, $resultEnd, $newData);
                        $currentIndex++;
                        break;
                    case IntersectResult::IS_CONTAINED:
                        array_splice($results, $currentIndex, 1, [
                            new NightIntervalData($resultStart, $inputStart, $oldData),
                            new NightIntervalData($inputStart, $inputEnd, $newData),
                            new NightIntervalData($inputEnd, $resultEnd, $oldData),
                        ]);
                        $resultsCount += 2;
                        continue 3; // next input
                }
            }
        }

        return new static($results);
    }

    /**
     * Splitter maps original data to a group of data. Should return array with keys indicating the data set group.
     *
     * @template TOther
     * @param callable(TData): array<int|string, TOther> $splitter
     * @return list<static<TOther>>
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
            $intervalSets[] = (new static($intervals))->normalize();
        }

        return $intervalSets; // @phpstan-ignore return.type (should return ..<DateIntervalDataSet<TOther>> but returns ..<DateIntervalDataSet<TData>> - TData from self and DateIntervalData somehow interpreted as the same type?)
    }

}
