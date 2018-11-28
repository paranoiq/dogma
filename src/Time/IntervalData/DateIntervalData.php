<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\IntervalData;

use Dogma\Check;
use Dogma\Comparable;
use Dogma\Equalable;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\InvalidIntervalStartEndOrderException;
use Dogma\Time\Span\DateSpan;
use Dogma\Time\Span\DateTimeSpan;
use function array_shift;
use function array_values;
use function usort;

/**
 * Interval of dates with data bound to it.
 */
class DateIntervalData implements Equalable, Comparable
{
    use StrictBehaviorMixin;

    public const MIN = Date::MIN;
    public const MAX = Date::MAX;

    /** @var \Dogma\Time\Date */
    private $start;

    /** @var \Dogma\Time\Date */
    private $end;

    /** @var mixed|null */
    private $data;

    /**
     * @param \Dogma\Time\Date $start
     * @param \Dogma\Time\Date $end
     * @param mixed|null $data
     */
    public function __construct(Date $start, Date $end, $data)
    {
        if ($start->getDayNumber() > $end->getDayNumber()) {
            throw new InvalidIntervalStartEndOrderException($start, $end);
        }

        $this->start = $start;
        $this->end = $end;
        $this->data = $data;
    }

    /**
     * @param \Dogma\Time\Interval\DateInterval $interval
     * @param mixed|null $data
     * @return self
     */
    public static function createFromDateInterval(DateInterval $interval, $data): self
    {
        return new static($interval->getStart(), $interval->getEnd(), $data);
    }

    public static function empty(): self
    {
        $interval = new static(new Date(), new Date(), null);
        $interval->start = new Date(self::MAX);
        $interval->end = new Date(self::MIN);

        return $interval;
    }

    /**
     * @param mixed|null $data
     * @return self
     */
    public static function all($data): self
    {
        return new static(new Date(self::MIN), new Date(self::MAX), $data);
    }

    // modifications ---------------------------------------------------------------------------------------------------

    /**
     * @param string $value
     * @return static
     */
    public function shift(string $value): self
    {
        return new static($this->start->modify($value), $this->end->modify($value), $this->data);
    }

    public function setStart(Date $start): self
    {
        return new static($start, $this->end, $this->data);
    }

    public function setEnd(Date $end): self
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
        return $this->isEmpty() ? 0 : $this->end->getDayNumber() - $this->start->getDayNumber();
    }

    public function getDayCount(): int
    {
        return $this->isEmpty() ? 0 : $this->end->getDayNumber() - $this->start->getDayNumber() + 1;
    }

    public function toDateInterval(): DateInterval
    {
        return new DateInterval($this->start, $this->end);
    }

    /**
     * @return \Dogma\Time\Date[]|\Dogma\Equalable[] array of pairs (Date $date, Equalable $data)
     */
    public function toDateDataArray(): array
    {
        if ($this->start->getDayNumber() > $this->end->getDayNumber()) {
            return [];
        }

        $date = $this->start;
        $dates = [];
        do {
            $dates[] = [$date, $this->data];
            $date = $date->addDay();
        } while ($date->isSameOrBefore($this->end));

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
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
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

        return $this->start->equals($other->start) && $this->end->equals($other->end) && $this->dataEquals($other->data);
    }

    /**
     * @param mixed|null $otherData
     * @return bool
     */
    public function dataEquals($otherData): bool
    {
        if ($this->data instanceof Equalable && $otherData instanceof Equalable) {
            return $this->data->equals($otherData);
        }

        return $this->data === $otherData;
    }

    /**
     * @param self $other
     * @return int
     */
    public function compare(Comparable $other): int
    {
        $other instanceof self || Check::object($other, self::class);

        return $this->start->compare($other->start)
            ?: $this->end->compare($other->end);
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

    /**
     * @param \Dogma\Time\Interval\DateInterval|\Dogma\Time\IntervalData\DateIntervalData $interval
     * @return bool
     */
    public function contains($interval): bool
    {
        if ($this->isEmpty() || $interval->isEmpty()) {
            return false;
        }

        return $this->start->isSameOrBefore($interval->getStart()) && $this->end->isSameOrAfter($interval->getEnd());
    }

    /**
     * @param \Dogma\Time\Interval\DateInterval|\Dogma\Time\IntervalData\DateIntervalData $interval
     * @return bool
     */
    public function intersects($interval): bool
    {
        return $this->containsValue($interval->getStart()) || $this->containsValue($interval->getEnd());
    }

    /**
     * @param \Dogma\Time\Interval\DateInterval|\Dogma\Time\IntervalData\DateIntervalData $interval
     * @return bool
     */
    public function touches($interval): bool
    {
        return $this->start->equals($interval->getEnd()->addDay()) || $this->end->equals($interval->getStart()->subtractDay());
    }

    // actions ---------------------------------------------------------------------------------------------------------

    public function intersect(DateInterval ...$items): self
    {
        $items[] = $this->toDateInterval();
        $items = DateInterval::sort($items);

        $result = array_shift($items);
        foreach ($items as $item) {
            if ($result->getEnd()->isSameOrAfter($item->getStart())) {
                $result = new DateInterval(Date::max($result->getStart(), $item->getStart()), Date::min($result->getEnd(), $item->getEnd()));
            } else {
                return static::empty();
            }
        }

        return new static($result->getStart(), $result->getEnd(), $this->data);
    }

    public function subtract(DateInterval ...$items): DateIntervalDataSet
    {
        $intervals = [$this];

        /** @var \Dogma\Time\Interval\DateInterval $item */
        foreach ($items as $item) {
            if ($item->isEmpty()) {
                continue;
            }
            /** @var self $interval */
            foreach ($intervals as $i => $interval) {
                unset($intervals[$i]);
                if ($interval->getStart()->isBefore($item->getStart()) && $interval->getEnd()->isAfter($item->getEnd())) {
                    $intervals[] = new static($interval->start, $item->getStart()->subtractDay(), $this->data);
                    $intervals[] = new static($item->getEnd()->addDay(), $interval->end, $this->data);
                } elseif ($interval->start->isBefore($item->getStart())) {
                    $intervals[] = new static($interval->start, Date::min($interval->end, $item->getStart()->subtractDay()), $this->data);
                } elseif ($interval->end->isAfter($item->getEnd())) {
                    $intervals[] = new static(Date::max($interval->start, $item->getEnd()->addDay()), $interval->end, $this->data);
                }
            }
        }

        return new DateIntervalDataSet(array_values($intervals));
    }

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sort(array $intervals): array
    {
        usort($intervals, function (DateIntervalData $a, DateIntervalData $b) {
            return $a->start->getDayNumber() <=> $b->start->getDayNumber() ?: $a->end->getDayNumber() <=> $b->end->getDayNumber();
        });

        return $intervals;
    }

    /**
     * @param self[] $intervals
     * @return self[]
     */
    public static function sortByStart(array $intervals): array
    {
        usort($intervals, function (DateIntervalData $a, DateIntervalData $b) {
            return $a->start->getDayNumber() <=> $b->start->getDayNumber();
        });

        return $intervals;
    }

}
