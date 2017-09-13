<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Money\CreditCard;

class InvalidCreditCardNumberException extends \Dogma\Exception
{

    /** @var string */
    private $number;

    public function __construct(string $number, ?\Throwable $previous = null)
    {
        parent::__construct('Given credit card number is not valid.', $previous);

        $this->number = $number;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

}
