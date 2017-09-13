<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Money;

use Dogma\Math\Decimal\Decimal;

class Money
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Money\Currency */
    private $currency;

    /** @var \Dogma\Math\Decimal\Decimal */
    private $amount;

    /** @var int */
    private $precision;

    public function __construct(Currency $currency, Decimal $amount)
    {
        $this->currency = $currency;
        $this->amount = $amount;
        $this->precision = $amount->getPrecision();
    }

    ///

}
