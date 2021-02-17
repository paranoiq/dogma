<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\StrictBehaviorMixin;

class Fraction
{
    use StrictBehaviorMixin;

    /** @var int */
    private $numerator;

    /** @var int */
    private $denominator;

    public function __construct(int $numerator, int $denominator)
    {
        $this->numerator = $numerator;
        $this->denominator = $denominator;
    }

    public static function simplified(int $numerator, int $denominator): self
    {
        $self = new self($numerator, $denominator);

        return $self->simplify();
    }

    public function __toString(): string
    {
        return "Fraction: {$this->numerator}/{$this->denominator}";
    }

    public function getNumerator(): int
    {
        return $this->numerator;
    }

    public function getDenominator(): int
    {
        return $this->denominator;
    }

    public function simplify(): Fraction
    {
        $gcd = IntCalc::greatestCommonDivider($this->numerator, $this->denominator);
        if ($gcd === 1) {
            return $this;
        }

        return new Fraction($this->numerator / $gcd, $this->denominator / $gcd);
    }

}
