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
use Dogma\Type;

/**
 * @method string format(string $format)
 */
trait DateDateTimeCommonMixin
{

    public function getDayOfWeekEnum(): DayOfWeek
    {
        return DayOfWeek::get((int) $this->format('N'));
    }

    /**
     * @param int|\Dogma\Time\DayOfWeek $day
     * @return bool
     */
    public function isDayOfWeek($day): bool
    {
        Check::types($day, [Type::INT, DayOfWeek::class]);

        if (is_int($day)) {
            $day = DayOfWeek::get($day);
        }

        return (int) $this->format('N') === $day->getValue();
    }

    public function isWeekend(): bool
    {
        return $this->format('N') > 5;
    }

    public function getMonthEnum(): Month
    {
        return Month::get((int) $this->format('n'));
    }

    /**
     * @param int|\Dogma\Time\Month $month
     * @return bool
     */
    public function isMonth($month): bool
    {
        Check::types($month, [Type::INT, Month::class]);

        if (is_int($month)) {
            $month = Month::get($month);
        }

        return (int) $this->format('n') === $month->getValue();
    }

}
