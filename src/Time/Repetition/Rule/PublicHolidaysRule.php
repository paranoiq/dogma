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

class PublicHolidaysRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    public function getNext(DateTime $after): DateTime
    {
        // TODO: Implement getNext() method.
    }

}
