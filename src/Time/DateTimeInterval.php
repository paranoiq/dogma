<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Check;

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

    /** @var int|float */
    private $seconds;

    /** @var int|null */
    private $totalDays;

    /**
     * @param int|string|\DateInterval $yearsSpec
     * @param int $months
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @param int|float $seconds
     * @param int|null $totalDays
     */
    public function __construct(
        $yearsSpec = 'P0D',
        int $months = 0,
        int $days = 0,
        int $hours = 0,
        int $minutes = 0,
        $seconds = 0,
        ?int $totalDays = null
    ) {
        if (is_numeric($yearsSpec)) {
            Check::int($yearsSpec);

            $this->years = $yearsSpec;
            $this->months = $months;
            $this->days = $days;
            $this->hours = $hours;
            $this->minutes = $minutes;
            $this->seconds = $seconds;
            $this->totalDays = $totalDays;
            return;
        }

        if ($yearsSpec instanceof \DateInterval) {
            $interval = $yearsSpec;
        } else {
            $interval = new \DateInterval($yearsSpec);
        }
        $this->years = $interval->invert ? -$interval->y : $interval->y;
        $this->months = $interval->invert ? -$interval->m : $interval->m;
        $this->days = $interval->invert ? -$interval->d : $interval->d;
        $this->hours = $interval->invert ? -$interval->h : $interval->h;
        $this->minutes = $interval->invert ? -$interval->i : $interval->i;
        $this->seconds = $interval->invert ? -$interval->s : $interval->s;
        $this->totalDays = $interval->days;
    }

    public static function createFromDateString(string $string): self
    {
        $self = new static(0);
        $interval = \DateInterval::createFromDateString($string);
        $self->years = $interval->y;
        $self->months = $interval->m;
        $self->days = $interval->d;
        $self->hours = $interval->h;
        $self->minutes = $interval->i;
        $self->seconds = $interval->s;

        return $self;
    }

    public function getYears(): int
    {
        return $this->years;
    }

    public function addYears(int $years): self
    {
        return new static($this->years + $years, $this->months, $this->days, $this->hours, $this->minutes, $this->seconds);
    }

    public function getMonths(): int
    {
        return $this->months;
    }

    public function addMonths(int $months): self
    {
        return new static($this->years, $this->months + $months, $this->days, $this->hours, $this->minutes, $this->seconds);
    }

    public function addWeeks(int $weeks): self
    {
        return new static($this->years, $this->months, $this->days + $weeks * 7, $this->hours, $this->minutes, $this->seconds);
    }

    public function getDays(): int
    {
        return $this->days;
    }

    public function addDays(int $days): self
    {
        return new static($this->years, $this->months, $this->days + $days, $this->hours, $this->minutes, $this->seconds);
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function addHours(int $hours): self
    {
        return new static($this->years, $this->months, $this->days, $this->hours + $hours, $this->minutes, $this->seconds);
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function addMinutes(int $minutes): self
    {
        return new self($this->years, $this->months, $this->days, $this->hours, $this->minutes + $minutes, $this->seconds);
    }

    /**
     * @return int|float
     */
    public function getSeconds()
    {
        return $this->seconds;
    }

    public function getSecondsFloored(): int
    {
        return (int) floor($this->seconds);
    }

    public function getMicroseconds(): int
    {
        return ($this->seconds - floor($this->seconds)) * 1000000;
    }

    /**
     * @param int|float $seconds
     * @return static
     */
    public function addSeconds($seconds): self
    {
        return new self($this->years, $this->months, $this->days, $this->hours, $this->minutes, $this->seconds + $seconds);
    }

    public function format(): string
    {
        ///
    }

}
