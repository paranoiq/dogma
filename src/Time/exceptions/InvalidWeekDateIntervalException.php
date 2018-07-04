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

class InvalidWeekDateIntervalException extends InvalidIntervalException
{

    public function __construct(DateOrTime $start, DateOrTime $end, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Interval start and end should be aligned to start and end of a week (monday and sunday). Start date %s and end date %s are not.', $start->format(), $end->format()),
            $previous
        );
    }

}
