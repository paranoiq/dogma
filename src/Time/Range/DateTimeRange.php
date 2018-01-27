<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Range;

use Dogma\Time\DateTime;

class DateTimeRange
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Time\DateTime */
    private $since;

    /** @var \Dogma\Time\DateTime */
    private $until;

    public function __construct(DateTime $since, DateTime $until)
    {
        $this->since = $since;
        $this->until = $until;
    }

}
