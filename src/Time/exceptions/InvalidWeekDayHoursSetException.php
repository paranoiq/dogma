<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use function sprintf;

class InvalidWeekDayHoursSetException extends TimeException
{

    public function __construct(DayOfWeek $dayOfWeek, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Each day of week can be specified only once in WeekDayHoursSet. %s was specified twice', $dayOfWeek->getName()), $previous);
    }

}
