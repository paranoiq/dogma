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
use Dogma\Math\Interval\IntInterval;
use Dogma\Math\Interval\IntervalParser;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;

/**
 * Interval of dates. Based on IntInterval.
 */
class DateInterval implements DateOrTimeInterval
{
    use StrictBehaviorMixin;

    public const MIN = Date::MIN;
    public const MAX = Date::MAX;

    public const DEFAULT_FORMAT = 'Y-m-d| - Y-m-d';

    /** @var \Dogma\Time\Date */
    private $start;

    /** @var \Dogma\Time\Date */
    private $end;

    public function __construct(Date $start, Date $end)
    {
        $this->start = $start;
        $this->end = $end;

        if ($start->getDayNumber() > $end->getDayNumber()) {
            throw new InvalidIntervalStartEndOrderException($this->start, $this->end);
        }
    }

    public static function createFromString(string $string): self
    {
        [$start, $end, $openStart, $openEnd] = IntervalParser::parseString($string);

        $start = new Date($start);
        $end = new Date($end);
        if ($openStart) {
            $start = $start->addDay();
        }
        if ($openEnd) {
            $end = $end->subtractDay();
        }
        if ($start->getDayNumber() > $end->getDayNumber()) {
            return self::empty();
        }

        return new static($start, $end);
    }

    public static function empty(): self
    {
        $interval = new static(new Date(), new Date());
        $interval->start = new Date(self::MAX);
        $interval->end = new Date(self::MIN);

        return $interval;
    }

    public static function all(): self
    {
        return new static(new Date(self::MIN), new Date(self::MAX));
    }

    // modifications ---------------------------------------------------------------------------------------------------

    /**
     * @param string $value
     * @return static
     */
    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value));
    }

    public function setStart(Date $start): self
    {
        return new static($start, $this->end);
    }

    public function setEnd(Date $end): self
    {
        return new static($this->start, $end);
    }

    // queries ---------------------------------------------------------------------------------------------------------

    public function getSpan(): DateTimeSpan
    {
        return DateTimeSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getDateSpan(): DateSpan
    {
        return DateSpan::createFromDateInterval($this->start->diff($this->end));
    }

    public function getLengthInDays(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getDayNumber() - $this->start->getDayNumber();
    }

    public function getDayCount(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getDayNumber() - $this->start->getDayNumber() + 1;
    }

    public function toDayNumberIntInterval(): IntInterval
    {
        return new IntInterval($this->start->getDayNumber(), $this->end->getDayNumber());
    }

    /**
     * @return \Dogma\Time\Date[]
     */
    public function toDateArray(): array
    {
        if ($this->start > $this->end) {
            return [];
        }

        $date = $this->start;
        $dates = [];
        do {
            $dates[] = $date;
            $date = $date->addDay();
        } while ($date->isSameOrBefore($this->end));

        return $dates;
    }

    public function format(string $format = self::DEFAULT_FORMAT, ?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new SimpleDateTimeIntervalFormatter();
        }

        return $formatter->format($this, $format);
    }

    public function getStart(): Date
    {
        return $this->start;
    }

    public function getEnd(): Date
    {
        return $this->end;
    }

    public function isEmpty(): bool
    {
        return $this->start->getDayNumber() > $this->end->getDayNumber();
    }

    /**
     * @param self $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->start->equals($other->start) && $this->end->equals($other->end);
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->start->compare($other->start) ?: $this->end->compare($other->end);
    }

    /**
     * @param \Dogma\Time\Date|\DateTimeInterface $date
     * @return bool
     */
    public function containsValue($date): bool
    {
        if (!$date instanceof Date) {
            $date = Date::createFromDateTimeInterface($date);
        }

        return $date->isBetween($this->start, $this->end);
    }

    public function contains(self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        return $this->start->isSameOrBefore($interval->start) && $this->end->isSameOrAfter($interval->end);
    }

    public function intersects(self $interval): bool
    {
        return $this->containsValue($interval->start) || $this->containsValue($interval->end);
    }

    /**
     * @param \Dogma\Time\Interval\DateInterval $interval
     * @return bool
     */
    public function touches(self $interval): bool
    {
        return $this->start->equals($interval->end->addDay()) || $this->end->equals($interval->start->subtractDay());
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function split(int $parts): DateIntervalSet
    {
        Check::min($parts, 1);

        if ($this->isEmpty()) {
            return new DateIntervalSet([]);
        }

        $partSize = ($this->end->getDayNumber() - $this->start->getDayNumber() + 1) / $parts;
        $intervalStarts = [];
        for ($n = 1; $n < $parts; $n++) {
            $intervalStarts[] = (int) round($this->start->getDayNumber() + $partSize * $n);
        }
        $intervalStarts = array_unique($intervalStarts);
        $intervalStarts = Arr::map($intervalStarts, function (int $dayNumber) {
            return Date::createFromDayNumber($dayNumber);
        });

        return $this->splitBy($intervalStarts);
    }

    /**
     * @param \Dogma\Time\Date $intervalStarts
     * @return \Dogma\Time\Interval\DateIntervalSet
     */
    public function splitBy(array $intervalStarts): DateIntervalSet
    {
        if ($this->isEmpty()) {
            return new DateIntervalSet([]);
        }

        $intervalStarts = Arr::sort($intervalStarts);
        $results = [$this];
        $i = 0;
        /** @var \Dogma\Time\Date $intervalStart */
        foreach ($intervalStarts as $intervalStart) {
            $interval = $results[$i];
            if ($interval->containsValue($intervalStart) && $interval->containsValue($intervalStart->subtractDay())) {
                $results[$i] = new static($interval->start, $intervalStart->subtractDay());
                $results[] = new static($intervalStart, $interval->end);
                $i++;
            }
        }

        return new DateIntervalSet($results);
    }

    public function envelope(self ...$items): self
    {
        $items[] = $this;
        $start = Date::MAX_DAY_NUMBER;
        $end = Date::MIN_DAY_NUMBER;
        /** @var self $item */
        foreach ($items as $item) {
            $startValue = $item->start->getDayNumber();
            if ($startValue < $start) {
                $start = $startValue;
            }
            $endValue = $item->end->getDayNumber();
            if ($endValue > $end) {
                $end = $endValue;
            }
        }

        return new static(new Date($start), new Date($end));
    }

    public function intersect(self ...$items): self
    {
        $items[] = $this;
        $items = self::sort($items);

        $result = array_shift($items);
        /** @var \Dogma\Time\Interval\DateInterval $item */
        foreach ($items as $item) {
            if ($result->end->isSameOrAfter($item->start)) {
                $result = new static(Date::max($result->start, $item->start), Date::min($result->end, $item->end));
            } else {
                return static::empty();
            }
        }

        return $result;
    }

    public function union(self ...$items): DateIntervalSet
    {
        $items[] = $this;
        $items = self::sortByStart($items);

        $current = array_shift($items);
        $results = [$current];
        /** @var self $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            if ($current->end->isSameOrAfter($item->start->subtractDay())) {
                $current = new static($current->start, Date::max($current->end, $item->end));
                $results[count($results) - 1] = $current;
            } else {
                $current = $item;
                $results[] = $current;
            }
        }

        return new DateIntervalSet($results);
    }

    public function difference(self ...$items): DateIntervalSet
    {
        $items[] = $this;
        $overlaps = self::countOverlaps(...$items);

        $results = [];
        foreach ($overlaps as $i => [$item, $count]) {
            if ($count === 1) {
                $results[] = $item;
            }
        }

        return new DateIntervalSet($results);
    }

    public function subtract(self ...$items): DateIntervalSet
    {
        $intervals = [$this];

        /** @var self $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            /** @var \Dogma\Time\Interval\DateInterval $interval */
            foreach ($intervals as $i => $interval) {
                unset($intervals[$i]);
                if ($interval->start->isBefore($item->start) && $interval->end->isAfter($item->end)) {
                    $intervals[] = new static($interval->start, $item->start->subtractDay());
                    $intervals[] = new static($item->end->addDay(), $interval->end);
                } elseif ($interval->start->isBefore($item->start)) {
                    $intervals[] = new static($interval->start, Date::min($interval->end, $item->start->subtractDay()));
                } elseif ($interval->end->isAfter($item->end)) {
                    $intervals[] = new static(Date::max($interval->start, $item->end->addDay()), $interval->end);
                }
            }
        }

        return new DateIntervalSet(array_values($intervals));
    }

    public function invert(): DateIntervalSet
    {
        return self::all()->subtract($this);
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Time\Interval\DateInterval[] ...$items
     * @return \Dogma\Time\Interval\DateInterval[][]|int[][] ($interval, $count)
     */
    public static function countOverlaps(self ...$items): array
    {
        $overlaps = self::explodeOverlaps(...$items);

        $results = [];
        /** @var \Dogma\Time\Interval\DateInterval $overlap */
        foreach ($overlaps as $overlap) {
            $ident = $overlap->toDayNumberIntInterval()->format();
            if (isset($results[$ident])) {
                $results[$ident][1]++;
            } else {
                $results[$ident] = [$overlap, 1];
            }
        }

        return array_values($results);
    }

    /**
     * @param \Dogma\Time\Interval\DateInterval ...$items
     * @return \Dogma\Time\Interval\DateInterval[]
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
            /** @var \Dogma\Time\Interval\DateInterval $b */
            foreach ($items as $j => $b) {
                if ($i === $j) {
                    // same item
                    continue;
                } elseif ($j < $starts[$i]) {
                    // already checked
                    continue;
                } elseif ($a->end->isBefore($b->start) || $a->start->isAfter($b->end)) {
                    // a1----a1    b1----b1
                } elseif ($a->start->equals($b->start)) {
                    if ($a->end->isAfter($b->end)) {
                        // a1=b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($b->end->addDay(), $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1=b1----a2=b2
                        // a1=b1----a2----b2
                    }
                } elseif ($a->start->isBefore($b->start)) {
                    if ($a->end->equals($b->end)) {
                        // a1----b1----a2=b2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start->subtractDay());
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } elseif ($a->end->isAfter($b->end)) {
                        // a1----b1----b2----a2
                        $items[$i] = $b;
                        $items[] = new static($a->start, $b->start->subtractDay());
                        $starts[count($items) - 1] = $i + 1;
                        $items[] = new static($b->end->addDay(), $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $b;
                    } else {
                        // a1----b1----a2----b2
                        $new = new static($b->start, $a->end);
                        $items[$i] = $new;
                        $items[] = new static($a->start, $b->start->subtractDay());
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    }
                } else {
                    if ($a->end->isAfter($b->end)) {
                        // b1----a1----b2----a2
                        $new = new static($a->start, $b->end);
                        $items[$i] = $new;
                        $items[] = new static($b->end->addDay(), $a->end);
                        $starts[count($items) - 1] = $i + 1;
                        $a = $new;
                    } else {
                        // b1----a1----a2=b2
                        // b1----a1----a2----b2
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
        return Arr::sortWith($intervals, function (DateInterval $a, DateInterval $b) {
            return $a->start->getDayNumber() <=> $b->start->getDayNumber() ?: $a->end->getDayNumber() <=> $b->end->getDayNumber();
        });
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        return Arr::sortWith($intervals, function (DateInterval $a, DateInterval $b) {
            return $a->start->getDayNumber() <=> $b->start->getDayNumber();
        });
    }

}
