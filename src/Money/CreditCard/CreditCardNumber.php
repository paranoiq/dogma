<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Money\CreditCard;

use Dogma\StrictBehaviorMixin;
use function substr;

class CreditCardNumber
{
    use StrictBehaviorMixin;

    /** @var string */
    private $number;

    public function __construct(string $number)
    {
        if (!self::validate($number)) {
            throw new InvalidCreditCardNumberException($number);
        }
        $this->number = $number;
    }

    public static function validate(string $number): bool
    {
        ///
    }

    public static function getIssuerByPrefix(string $prefix): CreditCardIssuer
    {
        ///
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getPrefix(): string
    {
        return substr($this->number, 0, 6);
    }

    public function getSegment(): string
    {
        ///

        return '';
    }

    public function getIssuer(): CreditCardIssuer
    {
        return self::getIssuerByPrefix($this->getPrefix());
    }

}
