<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition;

use Dogma\Time\Repetition\Rule\RepetitionRule;

/**
 * Definition:
 *  - Rule (Expression)
 *      - Rule (Condition)
 *      - Rule (Operator)
 *      - Rule (Condition)
 * - Bounds
 *      - DateRange
 *      - DateTimeRange
 *      - int $repetitionsCount
 */
class RepetitionDefinition
{

    /** @var \Dogma\Time\Repetition\Rule\RepetitionRule */
    private $rule;

    /** @var \Dogma\Time\Repetition\RepetitionBounds */
    private $bounds;

    public function __construct(RepetitionRule $rule, RepetitionBounds $bounds)
    {
        $this->rule = $rule;
        $this->bounds = $bounds;
    }

}
