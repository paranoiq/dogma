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
use Dogma\Time\DateTimeUnit;

class TimeUnitRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\DateTimeUnit */
    private $unit;

    /** @var int[] */
    private $ordinals;

    public function __construct(DateTimeUnit $unit, ?int ...$ordinals)
    {
        $this->unit = $unit;
        $this->ordinals = $ordinals;
    }

    public function getNext(DateTime $after): DateTime
    {
        // TODO: Implement getNext() method.
    }

}
