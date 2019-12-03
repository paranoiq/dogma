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
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\Math\Interval\Interval;
use Dogma\Math\Interval\IntervalParser;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTimeUnit;
use Dogma\Time\DayOfYear;
use Dogma\Time\InvalidDateTimeUnitException;
use Dogma\Time\InvalidDayOfYearIntervalException;
use function array_fill;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function round;
use function usort;

/**
 * Interval between two dates represented as DayOfYear. Does not include information about year.
 */
class DayOfYearInterval implements Interval
{
    use StrictBehaviorMixin;

    public const MIN = DayOfYear::MIN_NUMBER;
    public const MAX = DayOfYear::MAX_DENORMALIZED;

    public const DEFAULT_FORMAT = 'm-d| - m-d';

    /** @var \Dogma\Time\DayOfYear */
    private $start;

    /** @var \Dogma\Time\DayOfYear */
    private $end;

    public function __construct(DayOfYear $start, DayOfYear $end)
    {
        $startNumber = $start->getNumber();
        $endNumber = $end->getNumber();

        if ($startNumber > DayOfYear::MAX_NUMBER) {
            $startNumber %= DayOfYear::MAX_NUMBER;
            $start = $start->normalize();
        }
        if ($endNumber > DayOfYear::MAX_NUMBER) {
            $endNumber %= DayOfYear::MAX_NUMBER;
            $end = $end->normalize();
        }
        if ($startNumber > $endNumber) {
            $endNumber += DayOfYear::MAX_NUMBER;
            $end = $end->denormalize();
        }

        $length = $endNumber - $startNumber;
        if ($length > DayOfYear::MAX_NUMBER) {
            throw new InvalidDayOfYearIntervalException($start, $end);
        }

        $this->start = $start;
        $this->end = $end;
    }

    public static function createFromString(string $string): self
    {
        [$start, $end] = IntervalParser::parseString($string);

        $start = new DayOfYear($start);
        $end = new DayOfYear($end);

        return new static($start, $end);
    }

    public static function createFromStartAndLength(DayOfYear $start, DateTimeUnit $unit, int $amount): self
    {
        if (!$unit->isDate() || $unit->equals(DateTimeUnit::year())) {
            throw new InvalidDateTimeUnitException($unit);
        }
        if ($unit === DateTimeUnit::quarter()) {
            $unit = DateTimeUnit::month();
            $amount *= 3;
        }

        return new static($start, $start->modify('+' . $amount . ' ' . $unit->getValue()));
    }

    public static function empty(): self
    {
        return new static(new DayOfYear(self::MAX), new DayOfYear(self::MIN));
    }

    public static function all(): self
    {
        return new static(new DayOfYear(self::MIN), new DayOfYear(self::MAX));
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value));
    }

    public function setStart(DayOfYear $start): self
    {
        return new static($start, $this->end);
    }

    public function setEnd(DayOfYear $end): self
    {
        return new static($this->start, $end);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function toDateInterval(int $year): DateInterval
    {
        return new DateInterval($this->start->toDate($year), $this->end->toDate($year));
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this->toDateInterval(DayOfYear::DEFAULT_FORMAT_YEAR), $format);
    }

    public function getStart(): DayOfYear
    {
        return $this->start;
    }

    public function getEnd(): DayOfYear
    {
        return $this->end;
    }

    public function isEmpty(): bool
    {
        return $this->start->getNumber() > $this->end->getNumber();
    }

    public function isOverEndOfYear(): bool
    {
        return $this->end->getNumber() >= DayOfYear::MAX_NUMBER;
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->start->equals($other->start)
            && $this->end->getNumber() === $other->end->getNumber(); // cannot use DayOfYear::equals() because of normalized vs denormalized values
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start)
            ?: $this->end->getNumber() <=> $other->end->getNumber(); // cannot use DayOfYear::compare() because of normalized vs denormalized values
    }

    public function containsValue(DayOfYear $value): bool
    {
        $time = $value->normalize()->getNumber();
        $time2 = $value->denormalize()->getNumber();
        $startTime = $this->getStart()->getNumber();
        $endTime = $this->getEnd()->getNumber();

        return ($time >= $startTime && $time <= $endTime) || ($time2 >= $startTime && $time2 <= $endTime);
    }

    /**
     * @param \Dogma\Time\Interval\DayOfYearInterval $interval
     * @return bool
     */
    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        $intervalStart = $interval->start->getNumber();
        $intervalEnd = $interval->getEnd()->getNumber();
        $thisStart = $this->getStart()->getNumber();
        $thisEnd = $this->getEnd()->getNumber();

        return ($intervalStart >= $thisStart) && ($intervalEnd <= $thisEnd);
    }

    public function intersects(self $interval): bool
    {
        return $this->containsValue($interval->start)
            || $this->containsValue($interval->end)
            || $interval->containsValue($this->start)
            || $interval->containsValue($this->end)
            || ($this->start->equals($interval->start) && $this->end->equals($interval->end));
    }

    /**
     * @param \Dogma\Time\Interval\DayOfYearInterval $interval
     * @return bool
     */
    public function touches(self $interval): bool
    {
        return $this->start->equals($interval->getEnd()->addDay()) || $this->end->equals($interval->start->subtractDay());
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): DayOfYearIntervalSet
    {
        if ($this->isEmpty()) {
            return new DayOfYearIntervalSet([]);
        }

        $partSize = ($this->end->getNumber() - $this->start->getNumber()) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            // rounded to days
            $intervalStarts[] = round(($this->start->getNumber() + $partSize * $n) % (DayOfYear::MAX_NUMBER + 1));
        }
        $intervalStarts = array_unique($intervalStarts);
        $intervalStarts = Arr::map($intervalStarts, function (int $number) {
            return new DayOfYear($number);
        });

        return $this->splitBy($intervalStarts);
    }

    /**
     * @param \Dogma\Time\DayOfYear[] $intervalStarts
     * @return \Dogma\Time\Interval\DayOfYearIntervalSet
     */
    public function splitBy(array $intervalStarts): DayOfYearIntervalSet
    {
        if ($this->isEmpty()) {
            return new DayOfYearIntervalSet([]);
        }

        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        /** @var \Dogma\Time\DayOfYear $intervalStart */
        foreach ($intervalStarts as $intervalStart) {
            /** @var \Dogma\Time\Interval\DayOfYearInterval $interval */
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart)) {
                $results[$i] = new static($interval->start, $intervalStart);
                $results[] = new static($intervalStart->addDay(), $interval->end);
                $i++;
            }
        }

        return new DayOfYearIntervalSet($results);
    }

    /**
     * @return self[]
     */
    public function splitByEndOfYear(): array
    {
        if (!$this->isOverEndOfYear()) {
            return [$this, self::empty()];
        }

        return [
            new self($this->start, new DayOfYear(DayOfYear::MAX_NUMBER)),
            new self(new DayOfYear(DayOfYear::MIN_NUMBER), $this->end),
        ];
    }

    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = new DayOfYear(self::MAX);
        $end = new DayOfYear(self::MIN);
        /** @var \Dogma\Time\Interval\DayOfYearInterval $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($item->start->getNumber() < $start->getNumber()) {
                $start = $item->start;
            }
            if ($item->end->getNumber() > $end->getNumber()) {
                $end = $item->end;
            }
        }

        return new static($start, $end);
    }

    public function intersect(self ...$items): self
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        /** @var \Dogma\Time\Interval\DayOfYearInterval $result */
        $result = array_shift($items);
        /** @var \Dogma\Time\Interval\DayOfYearInterval $item */
        foreach ($items as $item) {
            if ($result->start->getNumber() < $item->start->getNumber()) {
                if ($result->end->getNumber() < $item->start->getNumber()) {
                    return self::empty();
                }
                $result = new static($item->start, $result->end);
            }
            if ($result->end->getNumber() > $item->end->getNumber()) {
                if ($result->start->getNumber() > $item->end->getNumber()) {
                    return self::empty();
                }
                $result = new static($result->start, $item->end);
            }
        }

        return $result;
    }

    public function union(self ...$items): DayOfYearIntervalSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var \Dogma\Time\Interval\DayOfYearInterval $item */
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

        return new DayOfYearIntervalSet($results);
    }

    public function difference(self ...$items): DayOfYearIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new DayOfYearIntervalSet($results);
    }

    public function subtract(self ...$items): DayOfYearIntervalSet
    {
        $results = [$this];

        /** @var \Dogma\Time\Interval\DayOfYearInterval $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            $itemStartTime = $item->getStart()->getNumber();
            $itemEndTime = $item->getEnd()->getNumber();
            /** @var \Dogma\Time\Interval\DayOfYearInterval $interval */
            foreach ($results as $r => $interval) {
                $intervalStartTime = $interval->getStart()->getNumber();
                $intervalEndTime = $interval->getEnd()->getNumber();

                $startLower = $intervalStartTime < $itemStartTime;
                $endHigher = $intervalEndTime > $itemEndTime;
                if ($startLower && $endHigher) {
                    // r1****i1----i2****r2
                    unset($results[$r]);
                    $results[] = new static($interval->start, $item->start);
                    $results[] = new static($item->end, $interval->end);
                } elseif ($startLower) {
                    if ($intervalEndTime < $itemStartTime) {
                        // r1****r2    i1----i2
                        continue;
                    } else {
                        // r1****i1----r2----i2
                        unset($results[$r]);
                        $results[] = new static($interval->start, $item->start);
                    }
                } elseif ($endHigher) {
                    if ($intervalStartTime > $itemEndTime) {
                        // i1----i2    r1****r2
                        continue;
                    } else {
                        // i1----r1----i2****r2
                        unset($results[$r]);
                        $results[] = new static($item->end, $interval->end);
                    }
                } else {
                    // i1----r1----r2----i2
                    unset($results[$r]);
                }
            }
        }

        return new DayOfYearIntervalSet(array_values($results));
    }

    public function invert(): DayOfYearIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Time\Interval\DayOfYearInterval ...$items
     * @return \Dogma\Time\Interval\DayOfYearInterval[][]|int[][] ($interval, $count)
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Time\Interval\DayOfYearInterval $overlap */
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
     * @param \Dogma\Time\Interval\DayOfYearInterval ...$items
     * @return \Dogma\Time\Interval\DayOfYearInterval[]
     */
    public static function explodeOverlaps(self ...$items): array
    {
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
            $aStart = $a->start->getNumber();
            $aEnd = $a->end->getNumber();
            /** @var \Dogma\Time\Interval\DayOfYearInterval $b */
            foreach ($items as $j => $b) {
                $bStart = $b->start->getNumber();
                $bEnd = $b->end->getNumber();
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                } elseif ($aEnd < $bStart || $aStart > $bEnd) {
                    // a1----a1    b1----b1
                    continue;
                } elseif ($aStart === $bStart) {
                    if ($aEnd > $bEnd) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end->addDay(), $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStart = $a->start->getNumber();
                        $aEnd = $a->end->getNumber();
                    } else {
                        // a1=b1----a2=b2
                        // a1=b1----a2----b2
                        continue;
                    }
                } elseif ($aStart < $bStart) {
                    if ($aEnd === $bEnd) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start->subtractDay());
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStart = $a->start->getNumber();
                        $aEnd = $a->end->getNumber();
                    } elseif ($aEnd > $bEnd) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start->subtractDay());
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end->addDay(), $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                        $aStart = $a->start->getNumber();
                        $aEnd = $a->end->getNumber();
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start->subtractDay());
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                        $aStart = $a->start->getNumber();
                        $aEnd = $a->end->getNumber();
                    }
                } else {
                    if ($aEnd > $bEnd) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end);
                        $items[$i] = $new;
                        $items[] = new static($b->end->addDay(), $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                        $aStart = $a->start->getNumber();
                        $aEnd = $a->end->getNumber();
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
        usort($intervals, function (DayOfYearInterval $a, DayOfYearInterval $b) {
            return $a->start->getNumber() <=> $b->start->getNumber() ?: $a->end->getNumber() <=> $b->end->getNumber();
        });

        return $intervals;
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        usort($intervals, function (DayOfYearInterval $a, DayOfYearInterval $b) {
            return $a->start->getNumber() <=> $b->start->getNumber();
        });

        return $intervals;
    }

}
