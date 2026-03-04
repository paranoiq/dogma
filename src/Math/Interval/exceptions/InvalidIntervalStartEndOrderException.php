<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Throwable;

class InvalidIntervalStartEndOrderException extends InvalidIntervalException
{

    /**
     */
    public function __construct(int|float $start, int|float $end, ?Throwable $previous = null)
    {
        parent::__construct("Start {$start} should be less than or equal to end {$end}.", $previous);
    }

}
