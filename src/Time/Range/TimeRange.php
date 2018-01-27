<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Range;

use Dogma\Time\Time;

class TimeRange
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Time\Time */
    private $since;

    /** @var \Dogma\Time\Time */
    private $until;

    public function __construct(Time $since, Time $until)
    {
        $this->since = $since;
        $this->until = $until;
    }

}
