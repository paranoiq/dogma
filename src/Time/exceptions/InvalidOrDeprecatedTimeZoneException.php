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

class InvalidOrDeprecatedTimeZoneException extends TimeException
{

    public function __construct(string $name, ?\Throwable $previous = null)
    {
        $message = sprintf(
            'Time zone name "%s" is not valid or is deprecated. See https://secure.php.net/manual/en/timezones.others.php for deprecated time zones info.',
            $name
        );

        parent::__construct($message, $previous);
    }

}
