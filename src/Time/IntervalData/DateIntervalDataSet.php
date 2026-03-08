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
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;
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
 * @implements IteratorAggregate<DateIntervalData<TData>>
 */
class DateIntervalDataSet implements Equalable, IteratorAggregate
{
    use StrictBehaviorMixin;

    /** @var array<DateIntervalData<TData>> */
    private array $intervals;

    /**
     * @param array<DateIntervalData<TData>> $intervals
     */
    final public function __construct(array $intervals)
    {
        $this->intervals = Arr::values(Arr::filter($intervals, static function (DateIntervalData $interval): bool {
            return !$interval->isEmpty();
        }));
    }

    /**
     * @param TData $data
     * @return static<TData>
     */
    public static function createFromDateIntervalSet(DateIntervalSet $set, mixed $data): static
    {
        $intervals = array_map(static function (DateInterval $interval) use ($data) {
            return DateIntervalData::createFromDateInterval($interval, $data);
        }, $set->getIntervals());

        return new static($intervals);
    }

    public function toDateIntervalSet(): DateIntervalSet
    {
        $intervals = [];
        /** @var DateIntervalData<TData> $interval */
        foreach ($this->intervals as $interval) {
            $intervals[] = $interval->toDateInterval();
        }

        return new DateIntervalSet($intervals);
    }

    /**
     * @return array<array{Date, TData}>
     */
    public function toDateDataArray(): array
    {
        $intervals = $this->normalize()->getIntervals();

        return array_merge(...array_map(static function (DateIntervalData $interval) {
            return $interval->toDateDataArray();
        }, $intervals));
    }

    /**
     * @return array<DateIntervalData<TData>>
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }

    /**
     * @return Traversable<DateIntervalData<TData>>
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
        /** @var array<DateIntervalData<TData>> $intervals */
        $intervals = Arr::sortComparableValues($this->intervals);
        $count = count($intervals) - 1;
        for ($n = 0; $n < $count; $n++) {
            $first = $intervals[$n];
            $second = $intervals[$n + 1];
            if ($first->dataEquals($second->getData()) && ($first->intersects($second) || $first->touches($second))) {
                $intervals[$n + 1] = new DateIntervalData(
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
        return $this->addIntervals(...$set->intervals);
    }

    /**
     * @param DateIntervalData<TData> ...$intervals
     * @return static<TData>
     */
    public function addIntervals(DateIntervalData ...$intervals): static
    {
        /** @var list<DateIntervalData<TData>> $merge */
        $merge = array_merge($this->intervals, $intervals);

        return new static($merge);
    }

    /**
     * Remove another set of intervals from this one.
     * @return static<TData>
     */
    public function subtract(DateIntervalSet $set): static
    {
        return $this->subtractIntervals(...$set->getIntervals());
    }

    /**
     * @return static<TData>
     */
    public function subtractIntervals(DateInterval ...$intervals): static
    {
        $sources = $this->intervals;
        $results = [];
        /** @var DateIntervalData<TData> $result */
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

        /** @var array<DateIntervalData<TData>> $results */
        $results = $results;

        return new static($results);
    }

    /**
     * Intersect with another set of intervals.
     * @return static<TData>
     */
    public function intersect(DateIntervalSet $set): static
    {
        return $this->intersectIntervals(...$set->getIntervals());
    }

    /**
     * @return static<TData>
     */
    public function intersectIntervals(DateInterval ...$intervals): static
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
     * @param callable(DateIntervalData<TData> $data): (self<TNewData>|DateIntervalData<TNewData>|array<DateIntervalData<TNewData>>) $mapper
     * @return static<TNewData>
     */
    public function map(callable $mapper): static
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof DateIntervalData) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } else {
                throw new ShouldNotHappenException('Expected DateIntervalData or DateIntervalDataSet or array of DateIntervalData.');
            }
        }

        return new static($results);
    }

    /**
     * @template TNewData
     * @param callable(DateIntervalData<TData> $data): (self<TNewData>|DateIntervalData<TNewData>|array<DateIntervalData<TNewData>>|null) $mapper
     * @return static<TNewData>
     */
    public function collect(callable $mapper): static
    {
        $results = [];
        foreach ($this->intervals as $interval) {
            $result = $mapper($interval);
            if ($result instanceof DateIntervalData) {
                $results[] = $result;
            } elseif (is_array($result)) {
                $results = array_merge($results, $result);
            } elseif ($result instanceof self) {
                $results = array_merge($results, $result->getIntervals());
            } elseif ($result === null) {
                continue;
            } else {
                throw new ShouldNotHappenException('Expected DateIntervalData or DateIntervalDataSet or array of DateIntervalData.');
            }
        }

        return new static($results);
    }

    /**
     * Maps data with mapper and collects intervals with non-null results.
     *
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
                $results[] = new DateIntervalData($interval->getStart(), $interval->getEnd(), $resultData);
            }
        }

        return new static($results); // @phpstan-ignore return.type (should return ..<DateIntervalDataSet<TOther>> but returns ..<DateIntervalDataSet<TData>> - TData from self and DateIntervalData somehow interpreted as the same type?)
    }

    /**
     * Apply another DateIntervalDataSet on this one with reduce function.
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
                    $results[$i] = new DateIntervalData($result->getStart(), $result->getEnd(), $newData);
                } elseif ($interval->getStart()->isAfter($result->getStart()) && $interval->getEnd()->isBefore($result->getEnd())) {
                    // r1====i1****i2====r1
                    $results[$i] = new DateIntervalData($interval->getStart(), $interval->getEnd(), $newData);
                    $results[] = new DateIntervalData($result->getStart(), $interval->getStart()->subtractDay(), $result->getData());
                    $results[] = new DateIntervalData($interval->getEnd()->addDay(), $result->getEnd(), $result->getData());
                } elseif ($interval->getStart()->isAfter($result->getStart())) {
                    // r1====i1****r2----i2
                    $results[$i] = new DateIntervalData($result->getStart(), $interval->getStart()->subtractDay(), $result->getData());
                    $results[] = new DateIntervalData($interval->getStart(), $result->getEnd(), $newData);
                } elseif ($interval->getEnd()->isBefore($result->getEnd())) {
                    // i1----r1****i2====r2
                    $results[] = new DateIntervalData($result->getStart(), $interval->getEnd(), $newData);
                    $results[$i] = new DateIntervalData($interval->getEnd()->addDay(), $result->getEnd(), $result->getData());
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
     * @param callable(TInput): array{0: Date, 1: Date} $mapper
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
                    $inputEnd->getJulianDay(),
                    $resultStart->getJulianDay(),
                    $resultEnd->getJulianDay()
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
                            new DateIntervalData($resultStart, $inputEnd, $newData),
                            new DateIntervalData($inputEnd->addDay(), $resultEnd, $oldData),
                        ]);
                        $resultsCount++;
                        continue 3; // next input
                    case IntersectResult::FITS_TO_END:
                        array_splice($results, $currentIndex, 1, [
                            new DateIntervalData($resultStart, $inputStart->subtractDay(), $oldData),
                            new DateIntervalData($inputStart, $resultEnd, $newData),
                        ]);
                        $resultsCount++;
                        continue 3; // next input
                    case IntersectResult::INTERSECTS_END:
                        array_splice($results, $currentIndex, 1, [
                            new DateIntervalData($resultStart, $inputStart->subtractDay(), $oldData),
                            new DateIntervalData($inputStart, $resultEnd, $newData),
                        ]);
                        $resultsCount++;
                        $currentIndex += 2;
                        break;
                    case IntersectResult::EXTENDS_START:
                    case IntersectResult::SAME:
                        $results[$currentIndex] = new DateIntervalData($resultStart, $resultEnd, $newData);
                        continue 3; // next input
                    case IntersectResult::EXTENDS_END:
                    case IntersectResult::CONTAINS:
                        $results[$currentIndex] = new DateIntervalData($resultStart, $resultEnd, $newData);
                        $currentIndex++;
                        break;
                    case IntersectResult::IS_CONTAINED:
                        array_splice($results, $currentIndex, 1, [
                            new DateIntervalData($resultStart, $inputStart->subtractDay(), $oldData),
                            new DateIntervalData($inputStart, $inputEnd, $newData),
                            new DateIntervalData($inputEnd->addDay(), $resultEnd, $oldData),
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
                $intervalGroups[$key][] = new DateIntervalData($interval->getStart(), $interval->getEnd(), $values);
            }
        }

        $intervalSets = [];
        foreach ($intervalGroups as $intervals) {
            $intervalSets[] = (new static($intervals))->normalize();
        }

        return $intervalSets; // @phpstan-ignore return.type (should return ..<DateIntervalDataSet<TOther>> but returns ..<DateIntervalDataSet<TData>> - TData from self and DateIntervalData somehow interpreted as the same type?)
    }

}
