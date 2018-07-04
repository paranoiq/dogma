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

class TimeZoneMismatchException extends TimeException
{

    public function __construct(\DateTimeZone $first, \DateTimeZone $second, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'DateTimes must have matching time zones. Time zones "%s" and "%s" given.',
            $first->getName(),
            $second->getName()
        );

        parent::__construct($message, $previous);
    }

}
