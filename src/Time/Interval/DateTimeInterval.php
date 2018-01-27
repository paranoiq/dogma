<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

/**
 * Replacement of \DateInterval
 * - immutable
 * - capable of holding mixed offsets (eg: "+1 year, -2 months")
 * - calculations and rounding
 * - microseconds
 *
 * Since interval is not anchored, exact size of some units is not known.
 * Therefore in calculations where different units are compared or calculated:
 * - every month has 30 days
 * - every year has 365 days
 * - every day has 24 hours
 * This means that calculations containing normalisation and denormalisation (eg: getXyzTotal()) are not commutative
 * and may lead to unexpected results. For precise time calculations use timestamp instead.
 */
class DateTimeInterval
{
    use \Dogma\StrictBehaviorMixin;

    /** @var  int */
    private $years;

    /** @var int */
    private $months;

    /** @var int */
    private $days;

    /** @var int */
    private $hours;

    /** @var int */
    private $minutes;

    /** @var int */
    private $seconds;

    /** @var int */
    private $microseconds = 0;

    /**
     * @param int $years
     * @param int $months
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int $seconds
     * @param int $microseconds
     */
    public function __construct(
        int $years,
        int $months = 0,
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        int $seconds = 0,
        int $microseconds = 0
    ) {
        $this->years = $years;
        $this->months = $months;
        $this->days = $days;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->seconds = $seconds;
        $this->microseconds = $microseconds;
    }

    public static function createFromDateInterval(\DateInterval $interval): self
    {
        $self = new static(0);
        $self->years = $interval->invert ? -$interval->y : $interval->y;
        $self->months = $interval->invert ? -$interval->m : $interval->m;
        $self->days = $interval->invert ? -$interval->d : $interval->d;
        $self->hours = $interval->invert ? -$interval->h : $interval->h;
        $self->minutes = $interval->invert ? -$interval->i : $interval->i;
        $self->seconds = $interval->invert ? -$interval->s : $interval->s;
        $self->microseconds = $interval->invert ? (int) (-$interval->f * 1000000) : (int) ($interval->f * 1000);

        return $self;
    }

    public static function createFromDateIntervalString(string $string): self
    {
        $interval = new \DateInterval($string);

        return self::createFromDateInterval($interval);
    }

    public static function createFromDateString(string $string): self
    {
        $interval = \DateInterval::createFromDateString($string);

        return self::createFromDateInterval($interval);
    }

    /**
     * Subtracts positive and negative values if needed
     * @return \DateInterval
     */
    public function toDateInterval(): \DateInterval
    {
        if ($this->isPositive() || $this->isNegative()) {
            return self::toDateIntervalSimple($this);
        }
        $that = $this;
        $inverted = false;
        if ($this->getYearsFraction() < 0.0) {
            $that = $this->invert();
            $inverted = true;
        }

        $years = $that->years;
        $months = $that->months;
        $days = $that->days;
        $hours = $that->hours;
        $minutes = $that->minutes;
        $seconds = $that->seconds;
        $microseconds = $that->microseconds;

        if ($microseconds < 0) {
            $moveSeconds = (int) ($microseconds / 1000000) - 1;
            $seconds += $moveSeconds;
            $microseconds += $moveSeconds * 1000000;
        }
        if ($seconds < 0) {
            $moveMinutes = (int) ($seconds / 60) - 1;
            $minutes += $moveMinutes;
            $seconds += $moveMinutes * 60;
        }
        if ($minutes < 0) {
            $moveHours = (int) ($minutes / 60) - 1;
            $hours += $moveHours;
            $minutes += $moveHours * 60;
        }
        if ($hours < 0) {
            $moveDays = (int) ($hours / 24) - 1;
            $days += $moveDays;
            $hours += $moveDays * 24;
        }
        if ($days < 0) {
            $moveMonths = (int) ($days / 30) - 1;
            $months += $moveMonths;
            $days += $moveMonths * 30;
        }
        if ($months < 0) {
            $moveYears = (int) ($years / 12) - 1;
            $years += $moveYears;
            $months += $moveYears * 12;
        }
        if ($years < 0) {
            throw new \Dogma\ShouldNotHappenException('Years should always be positive at this point.');
        }

        $interval = new self($years, $months, $days, $hours, $minutes, $seconds, $microseconds);
        if ($inverted) {
            $interval = $interval->invert();
        }

        return self::toDateIntervalSimple($interval);
    }

    private static function toDateIntervalSimple(self $that): \DateInterval
    {
        $interval = new \DateInterval('P0Y');

        if ($that->isNegative()) {
            $interval->invert = true;
            $that = $that->invert();
        }
        $interval->y = $that->years;
        $interval->m = $that->months;
        $interval->d = $that->days;
        $interval->h = $that->hours;
        $interval->i = $that->minutes;
        $interval->s = $that->seconds;
        // drop microseconds

        return $interval;
    }

    /**
     * Separates positive and negative values to two instances
     * @return \DateInterval[] ($positive, $negative)
     */
    public function toDateIntervals(): array
    {
        $positive = new \DateInterval('P0Y');
        $negative = new \DateInterval('P0Y');
        $negative->invert = true;

        if ($this->years >= 0) {
            $positive->y = $this->years;
        } else {
            $negative->y = -$this->years;
        }
        if ($this->months >= 0) {
            $positive->m = $this->months;
        } else {
            $negative->m = -$this->months;
        }
        if ($this->days >= 0) {
            $positive->d = $this->days;
        } else {
            $negative->d = -$this->days;
        }
        if ($this->hours >= 0) {
            $positive->h = $this->hours;
        } else {
            $negative->h = -$this->hours;
        }
        if ($this->minutes >= 0) {
            $positive->i = $this->minutes;
        } else {
            $negative->i = -$this->minutes;
        }
        if ($this->seconds >= 0) {
            $positive->s = $this->seconds;
        } else {
            $negative->s = -$this->seconds;
        }
        // drop microseconds

        return [$positive, $negative];
    }

    public function format(?DateTimeIntervalFormatter $formatter = null): string
    {
        if ($formatter === null) {
            $formatter = new DateTimeIntervalFormatter();
        }
        return $formatter->format($this);
    }

    public function isZero(): bool
    {
        return $this->years === 0
            && $this->months === 0
            && $this->days === 0
            && $this->hours === 0
            && $this->minutes === 0
            && $this->seconds === 0
            && $this->microseconds === 0;
    }

    public function isMixed(): bool
    {
        return !$this->isPositive() && !$this->isNegative();
    }

    private function isPositive(): bool
    {
        return $this->years >= 0
            && $this->months >= 0
            && $this->days >= 0
            && $this->hours >= 0
            && $this->minutes >= 0
            && $this->seconds >= 0
            && $this->microseconds >= 0;
    }

    private function isNegative(): bool
    {
        return $this->years < 0
            && $this->months < 0
            && $this->days < 0
            && $this->hours < 0
            && $this->minutes < 0
            && $this->seconds < 0
            && $this->microseconds < 0;
    }

    /**
     * @return int[]
     */
    public function getValues(): array
    {
        return [
            $this->years,
            $this->months,
            $this->days,
            $this->hours,
            $this->minutes,
            $this->seconds,
            $this->microseconds,
        ];
    }

    public function getYears(): int
    {
        return $this->years;
    }

    public function getYearsFraction(): float
    {
        return $this->years
            + $this->months / 12
            + $this->days / 365
            + $this->hours / 365 / 24
            + $this->minutes / 365 / 24 / 60
            + $this->seconds / 365 / 24 / 60 / 60
            + $this->microseconds / 365 / 24 / 60 / 60 / 1000000;
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function getMonthsTotal(): float
    {
        return $this->getMonthsFraction()
            + $this->years * 12;
    }

    public function getMonthsFraction(): float
    {
        return $this->months
            + $this->days / 30
            + $this->hours / 30 / 24
            + $this->minutes / 30 / 24 / 60
            + $this->seconds / 30 / 24 / 60 / 60
            + $this->microseconds / 30 / 24 / 60 / 60 / 1000000;
    }

    public function getWeeks(): int
    {
        return (int) floor($this->days / 7);
    }

    public function getWeeksTotal(): float
    {
        return $this->getDaysTotal() / 7;
    }

    public function getWeeksFraction(): float
    {
        return $this->getDaysFraction() / 7;
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function getDaysTotal(): float
    {
        return $this->getDaysFraction()
            + $this->months * 30
            + $this->years * 12 * 30;
    }

    public function getDaysFraction(): float
    {
        return $this->days
            + $this->hours / 24
            + $this->minutes / 24 / 60
            + $this->seconds / 24 / 60 / 60
            + $this->microseconds / 24 / 60 / 60 / 1000000;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function getHoursTotal(): float
    {
        return $this->getHoursFraction()
            + $this->days * 24
            + $this->months * 30 * 24
            + $this->years * 12 * 30 * 24;
    }

    public function getHoursFraction(): float
    {
        return $this->hours
            + $this->minutes / 60
            + $this->seconds / 60 / 60
            + $this->microseconds / 60 / 60 / 1000000;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getMinutesTotal(): float
    {
        return $this->getMinutesFraction()
            + $this->hours * 60
            + $this->days * 24 * 60
            + $this->months * 30 * 24 * 60
            + $this->years * 12 * 30 * 24 * 60;
    }

    public function getMinutesFraction(): float
    {
        return $this->minutes
            + $this->seconds / 60
            + $this->microseconds / 60 / 1000000;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function getSecondsTotal(): float
    {
        return $this->getSecondsFraction()
            + $this->minutes * 60
            + $this->hours * 60 * 60
            + $this->days * 24 * 60 * 60
            + $this->months * 30 * 24 * 60 * 60
            + $this->years * 12 * 30 * 24 * 60 * 60;
    }

    public function getSecondsFraction(): float
    {
        return $this->seconds
            + $this->microseconds / 1000000;
    }

    public function getMicroseconds(): int
    {
        return $this->microseconds;
    }

    /**
     * @return int|float
     */
    public function getMicrosecondsTotal()
    {
        return $this->microseconds
            + $this->seconds * 1000000
            + $this->minutes * 60 * 1000000
            + $this->hours * 60 * 60 * 1000000
            + $this->days * 24 * 60 * 60 * 1000000
            + $this->months * 30 * 24 * 60 * 60 * 1000000
            + $this->years * 12 * 30 * 24 * 60 * 60 * 1000000;
    }

    public function add(self $other): self
    {
        return (new self(
            $this->years + $other->years,
            $this->months + $other->months,
            $this->days + $other->days,
            $this->hours + $other->hours,
            $this->minutes + $other->minutes,
            $this->seconds + $other->seconds,
            $this->microseconds + $other->microseconds
        ))->normalize(true);
    }

    /**
     * Does not split bigger units to smaller. Instead adds inverted interval. Call normalize() for simplified result.
     * @param \Dogma\Time\Interval\DateTimeInterval $other
     * @return self
     */
    public function subtract(self $other): self
    {
        return $this->add($other->invert());
    }

    public function invert(): self
    {
        return new self(-$this->years, -$this->months, -$this->days, -$this->hours, -$this->minutes, -$this->seconds, -$this->microseconds);
    }

    public function abs(): self
    {
        if ($this->getYearsFraction() >= 0.0) {
            return $this;
        } else {
            return $this->invert();
        }
    }

    /**
     * Normalizes values by summarizing smaller units into bigger. eg: '34 days' -> '1 month, 4 days'
     * @param bool $safeOnly
     * @return self
     */
    public function normalize(bool $safeOnly = false): self
    {
        $microseconds = $this->microseconds;
        $seconds = $this->seconds;
        $minutes = $this->minutes;
        $hours = $this->hours;
        $days = $this->days;
        $months = $this->months;
        $years = $this->years;

        if ($microseconds >= 1000000) {
            $seconds += (int) ($microseconds / 1000000);
            $microseconds = $microseconds % 1000000;
        } elseif ($microseconds <= -1000000) {
            $seconds += (int) ($microseconds / 1000000);
            $microseconds = $microseconds % 1000000;
        }
        if ($seconds >= 60) {
            $minutes += (int) ($seconds / 60);
            $seconds = $seconds % 60;
        } elseif ($seconds <= -60) {
            $minutes += (int) ($seconds / 60);
            $seconds = $seconds % 60;
        }
        if ($minutes >= 60) {
            $hours += (int) ($minutes / 60);
            $minutes = $minutes % 60;
        } elseif ($minutes <= -60) {
            $hours += (int) ($minutes / 60);
            $minutes = $minutes % 60;
        }

        if ($safeOnly) {
            return new self($years, $months, $days, $hours, $minutes, $seconds, $microseconds);
        }

        if ($hours >= 24) {
            $days += (int) ($hours / 24);
            $hours = $hours % 24;
        } elseif ($hours <= -24) {
            $days += (int) ($hours / 24);
            $hours = $hours % 24;
        }
        if ($days >= 30) {
            $months += (int) ($days / 30);
            $days = $days % 30;
        } elseif ($days <= -30) {
            $months += (int) ($days / 30);
            $days = $days % 30;
        }
        if ($months >= 12) {
            $years += (int) ($months / 12);
            $months = $months % 12;
        } elseif ($months <= -12) {
            $years += (int) ($months / 12);
            $months = $months % 12;
        }

        return new self($years, $months, $days, $hours, $minutes, $seconds, $microseconds);
    }

    public function roundToTwoValues(bool $useWeeks = false): self
    {
        $years = $this->getYearsFraction();
        if (abs($years) >= 1) {
            $wholeYears = (int) round($years);
            $months = (int) round(($years - $wholeYears) * 12);
            if (abs($months) === 12) {
                $wholeYears += (int) ($months / 12);
                $months = 0;
            }
            return new self($wholeYears, $months);
        }

        $months = $this->getMonthsFraction();
        if (abs($months) >= 1) {
            $wholeMonths = (int) round($months);
            $days = (int) round(($months - $wholeMonths) * 30);
            if (abs($days) === 30) {
                $wholeMonths += (int) ($days / 30);
                $days = 0;
            }
            if ($useWeeks) {
                $days = (int) (round($days / 7) * 7);
            }
            return new self(0, $wholeMonths, $days);
        }

        if ($useWeeks) {
            $weeks = $this->getWeeksFraction();
            if (abs($weeks) >= 1) {
                $days = (int) round($this->getDaysFraction());
                return new self(0, 0, $days);
            }
        }

        $days = $this->getDaysFraction();
        if (abs($days) >= 1) {
            $wholeDays = (int) round($days);
            $hours = (int) round(($days - $wholeDays) * 24);
            if (abs($hours) === 24) {
                $days += (int) ($hours / 24);
                $hours = 0;
            }
            return new self(0, 0, $wholeDays, $hours);
        }

        $hours = $this->getHoursTotal();
        if (abs($hours) >= 1) {
            $wholeHours = (int) round($hours);
            $minutes = (int) round(($hours - $wholeHours) * 60);
            if (abs($minutes) === 60) {
                $hours += (int) ($minutes / 60);
                $minutes = 0;
            }
            return new self(0, 0, 0, $wholeHours, $minutes);
        }

        $minutes = $this->getMinutesFraction();
        if (abs($minutes) >= 1) {
            $wholeMinutes = (int) round($minutes);
            $seconds = (int) round(($minutes - $wholeMinutes) * 60);
            if (abs($seconds) === 60) {
                $minutes += (int) ($seconds / 60);
                $seconds = 0;
            }
            return new self(0, 0, 0, 0, $wholeMinutes, $seconds);
        }

        return new self(0, 0, 0, 0, 0, $this->seconds, $this->microseconds);
    }

    public function roundToSingleValue(bool $useWeeks = false): self
    {
        $years = (int) round($this->getYearsFraction());
        if (abs($years) >= 1) {
            return new self($years);
        }

        $months = (int) round($this->getMonthsFraction());
        if (abs($months) >= 1) {
            return new self(0, $months);
        }

        if ($useWeeks) {
            $weeks = (int) round($this->getWeeksFraction());
            if (abs($weeks) >= 1) {
                return new self(0, 0, $weeks * 7);
            }
        }

        $days = (int) round($this->getDaysFraction());
        if (abs($days) >= 1) {
            return new self(0, 0, $days);
        }

        $hours = (int) round($this->getHoursTotal());
        if (abs($hours) >= 1) {
            return new self(0, 0, 0, $hours);
        }

        $minutes = (int) round($this->getMinutesFraction());
        if (abs($minutes) >= 1) {
            return new self(0, 0, 0, 0, $minutes);
        }

        $seconds = (int) round($this->getSecondsFraction());
        if (abs($seconds) >= 1) {
            return new self(0, 0, 0, 0, 0, $seconds);
        }

        return new self(0, 0, 0, 0, 0, 0, $this->microseconds);
    }

}
