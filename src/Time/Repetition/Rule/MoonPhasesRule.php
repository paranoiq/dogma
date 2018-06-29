<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition\Rule;

use Dogma\Math\Astronomy\MoonPhase;
use Dogma\Math\Astronomy\MoonPhaseCalculator;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;

class MoonPhasesRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Math\Astronomy\MoonPhase */
    private $moonPhase;

    public function __construct(MoonPhase $moonPhase)
    {
        $this->moonPhase = $moonPhase;
    }

    public function getNext(DateTime $after): ?DateTime
    {
        $calculator = new MoonPhaseCalculator();

        return $calculator->getNextDateTime($this->moonPhase, $after);
    }

}
