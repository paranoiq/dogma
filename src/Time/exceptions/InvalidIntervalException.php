<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

class InvalidIntervalException extends TimeException
{

    /**
     * @param \Dogma\Time\DateOrTime $start
     * @param \Dogma\Time\DateOrTime $end
     * @param \Throwable|null $previous
     */
    public function __construct(DateOrTime $start, DateOrTime $end, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Start %s should be less than or equal to end %s.', $start->format(), $end->format()),
            $previous
        );
    }

}
