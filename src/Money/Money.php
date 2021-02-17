<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Money;

use Dogma\Math\Decimal\Decimal;
use Dogma\StrictBehaviorMixin;

class Money
{
    use StrictBehaviorMixin;

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

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

}
