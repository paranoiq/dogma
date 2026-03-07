<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\IntervalData;

use DateTimeInterface;
use Dogma\Arr;
use Dogma\Check;
use Dogma\Cls;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\IntersectComparable;
use Dogma\Math\Interval\IntervalCalc;
use Dogma\Obj;
use Dogma\Pokeable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;
use function array_shift;
use function array_values;
use function sprintf;

/**
 * Interval of nights with data bound to it.
 */
class NightIntervalData implements Equalable, Comparable, IntersectComparable, Pokeable
{
    use StrictBehaviorMixin;

    public const MIN = Date::MIN;
    public const MAX = Date::MAX;

    private Date $start;

    private Date $end;

    private mixed $data;

    final public function __construct(Date $start, Date $end, mixed $data)
    {
        if ($start->getJulianDay() > $end->getJulianDay()) {
            throw new InvalidIntervalStartEndOrderException($start, $end);
        }

        $this->start = $start;
        $this->end = $end;
        $this->data = $data;
    }

    public static function createFromNightInterval(NightInterval $interval, mixed $data): static
    {
        return new static($interval->getStart(), $interval->getEnd(), $data);
    }

    public static function empty(): static
    {
        $interval = new static(new Date(), new Date(), null);
        $interval->start = new Date(self::MAX);
        $interval->end = new Date(self::MIN);

        return $interval;
    }

    public static function all(mixed $data): static
    {
        return new static(new Date(self::MIN), new Date(self::MAX), $data);
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function poke(): void
    {
        $this->start->format();
        $this->end->format();
    }

    /**
     * @deprecated replaced by https://github.com/paranoiq/dogma-debug/
     */
    public function dump(): string
    {
        return sprintf(
            '%s(%s - %s %s #%s)',
            Cls::short(static::class),
            $this->start->dump(),
            $this->end->dump(),
            '...',
            Obj::dumpHash($this)
        );
    }

    // modifications ---------------------------------------------------------------------------------------------------

    public function shift(string $value): static
    {
        return new static($this->start->modify($value), $this->end->modify($value), $this->data);
    }

    public function setStart(Date $start): static
    {
        return new static($start, $this->end, $this->data);
    }

    public function setEnd(Date $end): static
    {
        return new static($this->start, $end, $this->data);
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
        return $this->isEmpty() ? 0 : $this->end->getJulianDay() - $this->start->getJulianDay();
    }

    public function getDayCount(): int
    {
        return $this->getLengthInDays();
    }

    public function toNightInterval(): NightInterval
    {
        return new NightInterval($this->start, $this->end);
    }

    /**
     * @return array<array{Date, mixed}> array of pairs (Date $date, mixed $data)
     */
    public function toDateDataArray(): array
    {
        if ($this->start->getJulianDay() > $this->end->getJulianDay()) {
            return [];
        }

        $date = $this->start;
        $dates = [];
        do {
            $dates[] = [$date, $this->data];
            $date = $date->addDay();
        } while ($date->isBefore($this->end));

        return $dates;
    }

    public function getStart(): Date
    {
        return $this->start;
    }

    public function getEnd(): Date
    {
        return $this->end;
    }

    /**
     * @return array<Date>
     */
    public function getStartEnd(): array
    {
        return [$this->start, $this->end];
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return $this->start->getJulianDay() > $this->end->getJulianDay();
    }

    /**
     * @param self $other
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->start->equals($other->start) && $this->end->equals($other->end) && $this->dataEquals($other->data);
    }

    public function dataEquals(mixed $otherData): bool
    {
        if ($this->data instanceof Equalable && $otherData instanceof Equalable && $this->data::class === $otherData::class) {
            return $this->data->equals($otherData);
        }

        return $this->data === $otherData;
    }

    /**
     * @param self $other
     */
    public function compare(Comparable $other): int
    {
        Check::instance($other, self::class);

        return $this->start->compare($other->start)
            ?: $this->end->compare($other->end);
    }

    /**
     * @param self $other
     */
    public function compareIntersects(IntersectComparable $other): int
    {
        Check::instance($other, self::class);

        return IntervalCalc::compareIntersects(
            $this->start->getJulianDay(),
            $this->end->getJulianDay() - 1,
            $other->start->getJulianDay(),
            $other->end->getJulianDay() - 1
        );
    }

    public function containsValue(Date|DateTimeInterface $date): bool
    {
        if (!$date instanceof Date) {
            $date = Date::createFromDateTimeInterface($date);
        }

        return $date->isBetween($this->start, $this->end->subtractDay());
    }

    public function contains(NightInterval|self $interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        return $this->start->isSameOrBefore($interval->getStart()) && $this->end->isSameOrAfter($interval->getEnd());
    }

    public function intersects(NightInterval|self $interval): bool
    {
        return $this->start->isBefore($interval->getEnd()) && $this->end->isAfter($interval->getStart());
    }

    public function touches(NightInterval|self $interval): bool
    {
        return $this->start->equals($interval->getEnd()) || $this->end->equals($interval->getStart());
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function intersect(NightInterval ...$items): static
    {
        $items[] = $this->toNightInterval();
        $sorted = Arr::sortComparable($items);

        $result = array_shift($sorted);
        foreach ($sorted as $item) {
            if ($result->getEnd()->isSameOrAfter($item->getStart())) {
                $result = new NightInterval(Date::max($result->getStart(), $item->getStart()), Date::min($result->getEnd(), $item->getEnd()));
            } else {
                return static::empty();
            }
        }

        return new static($result->getStart(), $result->getEnd(), $this->data);
    }

    public function subtract(NightInterval ...$items): NightIntervalDataSet
    {
        $intervals = [$this];

        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            foreach ($intervals as $i => $interval) {
                unset($intervals[$i]);
                if ($interval->start->isBefore($item->getStart()) && $interval->end->isAfter($item->getEnd())) {
                    $intervals[] = new static($interval->start, $item->getStart(), $this->data);
                    $intervals[] = new static($item->getEnd(), $interval->end, $this->data);
                } elseif ($interval->start->isBefore($item->getStart())) {
                    $intervals[] = new static($interval->start, Date::min($interval->end, $item->getStart()), $this->data);
                } elseif ($interval->end->isAfter($item->getEnd())) {
                    $intervals[] = new static(Date::max($interval->start, $item->getEnd()), $interval->end, $this->data);
                }
            }
        }

        return new NightIntervalDataSet(array_values($intervals));
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param array<self> $intervals
     * @return array<self>
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sort(array $intervals): array
    {
        return Arr::sortComparable($intervals);
    }

    /**
     * @param array<self> $intervals
     * @return array<self>
     * @deprecated will be removed. use Arr::sortComparable() instead.
     */
    public static function sortByStart(array $intervals): array
    {
        return Arr::sortComparable($intervals);
    }

}
