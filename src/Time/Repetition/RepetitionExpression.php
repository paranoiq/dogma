<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition;

use Dogma\Time\DateTime;
use Dogma\Time\Repetition\Rule\RepetitionRule;

class RepetitionExpression implements RepetitionRule
{

    /** @var \Dogma\Time\Repetition\RepetitionOperator */
    private $operator;

    /** @var \Dogma\Time\Repetition\Rule\RepetitionRule */
    private $operand1;

    /** @var \Dogma\Time\Repetition\Rule\RepetitionRule */
    private $operand2;

    public function __construct(RepetitionRule $operand1, RepetitionOperator $operator, RepetitionRule $operand2)
    {
        $this->operator = $operator;
        $this->operand1 = $operand1;
        $this->operand2 = $operand2;
    }

    public function getNext(DateTime $after): ?DateTime
    {
        // TODO: Implement getNext() method.
    }

}
