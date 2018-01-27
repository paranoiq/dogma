<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Range;

use Dogma\Time\Date;

class DateRange
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Time\Date */
    private $since;

    /** @var \Dogma\Time\Date */
    private $until;

    public function __construct(Date $since, Date $until)
    {
        $this->since = $since;
        $this->until = $until;
    }

}