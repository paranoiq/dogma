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

class ValueOutOfAllowedRangeException extends InvalidIntervalException
{

    /**
     */
    public function __construct(int|float $value, int|float $min, int|float $max, ?Throwable $previous = null)
    {
        parent::__construct("Value {$value} is out of allowed range {$min} - {$max}.", $previous);
    }

}
