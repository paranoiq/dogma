<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition\Ical;

use Dogma\StrictBehaviorMixin;

class IcalNode
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Time\Repetition\Ical\IcalNodeType */
    private $type;

    /** @var mixed|mixed[] */
    private $values;

    /** @var mixed[] */
    private $params;

    public function __construct(IcalNodeType $type, $values, array $params = [])
    {
        $this->type = $type;
        $this->values = $values;
        $this->params = $params;
    }

}
