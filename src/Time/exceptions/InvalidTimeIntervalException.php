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

class InvalidTimeIntervalException extends TimeException
{

    public function __construct(Time $start, Time $end, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf(
            'Difference between two time values in a time interval must be less than or exactly 24 hours. Values %s (start) and %s (end) given.',
            $start->format(),
            $end->format()
        ), $previous);
    }

}
