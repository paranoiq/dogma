<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition\Rule;

use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;

class EasterRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    public function getNext(DateTime $after): DateTime
    {
        $year = $after->getYear();

        $timestamp = easter_date($year);
        $dateTime = DateTime::createFromTimestamp($timestamp, $after->getTimezone());
        if ($dateTime->isAfter($after)) {
            return $dateTime;
        }

        $timestamp = easter_date($year + 1);

        return DateTime::createFromTimestamp($timestamp, $after->getTimezone());
    }

}
