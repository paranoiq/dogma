<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Decimal;

use Dogma\ComparisonResult;
use Dogma\Round;
use Dogma\Sign;
use Dogma\Str;

/**
 * Decimal number implemented using bcmath.
 *
 * Bcmath scale is calculated so there is no precision loss in basic operations; with the exception of power() and root().
 * If the arbitrary precise result won't fit into the configured precision, a ValueOutOfBoundsException is thrown.
 * So it is sure, there are no rounding errors at all.
 */
class Decimal
{
    use \Dogma\StrictBehaviorMixin;

    private const MAX_SIZE = 65;

    /** @var string */
    private $value;

    /** @var int */
    private $size;

    /** @var int */
    private $precision;

    /** @var bool */
    private $integer;

    /**
     * @param string|int $value
     * @param int $size total number of digits
     * @param int $precision number of digits after decimal point
     */
    public function __construct($value, ?int $size = null, ?int $precision = null)
    {
        if (is_int($value)) {
            $value = (string) $value;
        } elseif (!is_string($value)) {
            throw new \Dogma\InvalidTypeException('string|int', $value);
        }

        list($sign, $int, $fraction) = self::parse($value);

        if ($precision === null) {
            $precision = strlen($fraction);
        }
        if ($size === null) {
            $size = strlen($int) + $precision;
        }

        self::checkSize($size, $precision);

        $this->value = $sign . $int . ($fraction ? '.' . $fraction : '');

        if (strlen($int) > ($size - $precision) || strlen($fraction) > $precision) {
            throw new \Dogma\ValueOutOfBoundsException($this->value, sprintf('%s(%s,%s)', self::class, $size, $precision));
        }

        $this->size = $size;
        $this->precision = $precision;
        $this->integer = !$fraction;
    }

    public static function checkSize(int $size, int $precision): void
    {
        if ($size > self::MAX_SIZE || $size < 1 || $precision < 0) {
            throw new \Dogma\ValueOutOfBoundsException(sprintf('of type %s(%s,%s)', self::class, $size, $precision), self::class . '(65)');
        }
    }

    /**
     * @param string $value
     * @return string[]
     */
    private static function parse(string $value): array
    {
        $pos = strpos($value, '.');
        if ($pos === false) {
            $int = $value;
            $fraction = '';
        } else {
            $int = substr($value, 0, $pos);
            $fraction = rtrim(substr($value, $pos + 1), '0');
        }
        $sign = '';
        if ($value[0] === '-') {
            $sign = '-';
            $int = substr($int, 1);
        }

        return [$sign, $int, $fraction];
    }

    public function __toString(): string
    {
        return sprintf('Decimal(%d,%d): %s', $this->size, $this->precision, $this->value);
    }

    // getters ---------------------------------------------------------------------------------------------------------

    public function getSize(): int
    {
        return $this->size;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getMaxValue(): self
    {
        return new self(
            str_repeat('9', $this->size - $this->precision) . ($this->precision ? '.' . str_repeat('9', $this->precision) : ''),
            $this->size,
            $this->precision
        );
    }

    public function getCurrentSize(): int
    {
        return strlen(str_replace(['-', '.'], ['', ''], $this->value));
    }

    public function getCurrentPrecision(): int
    {
        return strlen(Str::fromFirst($this->value, '.'));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getSign(): int
    {
        return $this->value === '0' ? Sign::NEUTRAL : ($this->value[0] === '-' ? Sign::NEGATIVE : Sign::POSITIVE);
    }

    public function isPositive(): bool
    {
        return $this->value !== '0' && $this->value[0] !== '-';
    }

    public function isNegative(): bool
    {
        return $this->value[0] === '-';
    }

    public function isZero(): bool
    {
        return $this->value === '0';
    }

    public function isInteger(): bool
    {
        return $this->integer;
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $root
     * @return bool
     */
    public function isPowerOf($root): bool
    {
        if (!$root instanceof self) {
            $root = new self($root);
        }
        try {
            $that = $this;
            switch (true) {
                case $root->isZero() || $that->isZero():
                    return false;
                case $root->greaterThan(1):
                    while ($that->greaterThan(1)) {
                        $that = $that->divide($root);
                        if ($that->getValue() === '1') {
                            return true;
                        }
                    }
                    break;
                case $root->lessThan(-1):
                    while ($that->greaterThan(1) || $that->lessThan(-1)) {
                        $that = $that->divide($root);
                        if ($that->getValue() === '1') {
                            return true;
                        }
                    }
                    break;
                case $root->greaterThan(0) && $root->lessThan(1):
                    while ($that->greaterThan(0) && $that->lessThan(1)) {
                        $that = $that->divide($root);
                        if ($that->getValue() === '1') {
                            return true;
                        }
                    }
                    break;
                case $root->greaterThan(-1) && $root->lessThan(0):
                    while ($that->greaterThan(-1) && $that->lessThan(1)) {
                        $that = $that->divide($root);
                        if ($that->getValue() === '1') {
                            return true;
                        }
                    }
                    break;
            }
            return false;
        } catch (\Dogma\ValueOutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return bool
     */
    public function isDivisibleBy($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        try {
            return $this->divide($other)->isInteger();
        } catch (\Dogma\ValueOutOfBoundsException $e) {
            return false;
        }
    }

    public function toFraction(): Fraction
    {
        $exponent = $this->getCurrentPrecision();
        if ($exponent > 0) {
            $value = $this->getValue();
            $numerator = new self(str_replace('.', '', $value), $this->getCurrentSize() + $exponent + 2, 2);

            return self::createSimplifiedFraction($numerator, $exponent);
        } else {
            return new Fraction($this, $this->setValue(1));
        }
    }

    private static function createSimplifiedFraction(Decimal $numerator, int $exponent): Fraction
    {
        $denominator = $numerator->setValue('1' . str_repeat('0', $exponent));
        $factors = [];
        while (count($factors) < $exponent) {
            if ($numerator->isDivisibleBy(2)) {
                $factors[] = 2;
                $numerator = $numerator->divide(2);
                $denominator = $denominator->divide(2);
            } else {
                break;
            }
        }
        while (count($factors) < $exponent) {
            if ($numerator->isDivisibleBy(5)) {
                $factors[] = 5;
                $numerator = $numerator->divide(5);
                $denominator = $denominator->divide(5);
            } else {
                break;
            }
        }

        return new Fraction($numerator, $denominator);
    }

    // operators -------------------------------------------------------------------------------------------------------

    /**
     * @param int|string $value
     * @return self
     */
    public function setValue($value): self
    {
        if (is_int($value)) {
            $value = (string) $value;
        } elseif (!is_string($value)) {
            throw new \Dogma\InvalidTypeException('string|int', $value);
        }
        return new self($value, $this->getSize(), $this->getPrecision());
    }

    public function setSize(int $size, ?int $precision = null): self
    {
        return new self($this->value, $size, $precision !== null ? $precision : $this->precision);
    }

    public function setPrecision(int $precision): self
    {
        return new self($this->value, $this->size, $precision);
    }

    public function abs(): self
    {
        if ($this->value[0] === '-') {
            return new self(substr($this->value, 1), $this->size, $this->precision);
        }
        return $this;
    }

    public function negate(): self
    {
        if ($this->value[0] === '-') {
            return new self(substr($this->value, 1), $this->size, $this->precision);
        } else {
            return new self('-' . $this->value, $this->size, $this->precision);
        }
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal$other
     * @return self
     */
    public function add($other): self
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        $value = bcadd($this->value, $other->value, max($this->precision, $other->precision));

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return self
     */
    public function subtract($other): self
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        $value = bcsub($this->value, $other->value, max($this->precision, $other->precision));

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $multiplier
     * @return self
     */
    public function multiply($multiplier): self
    {
        if (!$multiplier instanceof self) {
            $multiplier = new self($multiplier);
        }
        $value = bcmul($this->value, $multiplier->value, $this->precision + $multiplier->precision + 1);

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $divisor
     * @return self
     */
    public function divide($divisor): self
    {
        if (!$divisor instanceof self) {
            $divisor = new self($divisor);
        }
        $value = bcdiv($this->value, $divisor->value, $this->precision + $divisor->precision + 1);

        return new self($value, $this->size, $this->precision);
    }

    /**
     * Returns result rounded to given precision and reminder over that precision.
     * @param int|string|\Dogma\Math\Decimal\Decimal $divisor
     * @return self[]
     */
    public function divideWithReminder($divisor): array
    {
        if (!$divisor instanceof self) {
            $divisor = new self($divisor);
        }
        $int = (new self(bcdiv($this->value, $divisor->value, 6), self::MAX_SIZE, 6))->clip();
        $divisor = $divisor->setSize($divisor->getSize() + $int->getCurrentSize());
        $reminder = $this->subtract($divisor->multiply($int));

        return [$int->setSize($this->size, $this->precision), $reminder];
    }

    /**
     * Returns remainder after division computed with symmetrical algorithm.
     * If no precision is given, returned value can have higher precision than left operand value.
     * @param int|string|\Dogma\Math\Decimal\Decimal $divisor
     * @return self
     */
    public function reminder($divisor): self
    {
        if (!$divisor instanceof self) {
            $divisor = new self($divisor);
        }
        return $this->divideWithReminder($divisor)[1];
    }

    /**
     * Returns reminder after integer division.
     * Always returns an integer. Fraction part of both operands is trimmed.
     * @param int|string|\Dogma\Math\Decimal\Decimal $modulus
     * @return self
     */
    public function integerModulo($modulus): self
    {
        if (!$modulus instanceof self) {
            $modulus = new self($modulus);
        }
        $value = bcmod($this->value, $modulus->value);

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $nth
     * @return self
     */
    public function power($nth): self
    {
        if (!$nth instanceof self) {
            $nth = new self($nth);
        }
        if (!$nth->isInteger()) {
            throw new \Dogma\Math\Decimal\DecimalArithmeticException($nth, $this, sprintf(
                'Only integer powers are supported. Cannot power by %s.',
                $nth->getValue()
            ));
        }
        $value = bcpow($this->value, $nth->value, $this->precision * 2);

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $nth
     * @return self
     */
    public function root($nth): self
    {
        if (!$nth instanceof self) {
            $nth = new self($nth);
        }
        if ($nth->value === '2') {
            $value = bcsqrt($this->value, $this->precision * 2);
        } else {
            $value = bcpow($this->value, bcdiv('1', $nth->value, $this->precision * 2), $this->precision * 2);
        }

        return new self($value, $this->size, $this->precision);
    }

    public function sqrt(): self
    {
        return new self(bcsqrt($this->value, $this->precision * 2), $this->size, $this->precision);
    }

    // rounding --------------------------------------------------------------------------------------------------------

    public function round(int $fractionDigits, int $roundingRule = Round::NORMAL): self
    {
        $modulus = new self(bcpow(10, -$fractionDigits), $this->size, $fractionDigits);

        return $this->roundTo($modulus, $roundingRule);
    }

    public function roundUp(int $fractionDigits): self
    {
        return $this->round($fractionDigits, Round::UP);
    }

    public function roundDown(int $fractionDigits): self
    {
        return $this->round($fractionDigits, Round::DOWN);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $modulus
     * @param int $roundingRule
     * @return self
     */
    public function roundTo($modulus, int $roundingRule = Round::NORMAL): self
    {
        if (!$modulus instanceof self) {
            $modulus = new self($modulus);
        }
        $reminder = bcmod($this->value, $modulus->value);
        if ($reminder === '0') {
            return $this;
        }

        if ($roundingRule === Round::TOWARDS_ZERO) {
            if ($this->isNegative()) {
                $roundingRule = Round::UP;
            } else {
                $roundingRule = Round::DOWN;
            }
        } elseif ($roundingRule === Round::AWAY_FROM_ZERO) {
            if ($this->isNegative()) {
                $roundingRule = Round::DOWN;
            } else {
                $roundingRule = Round::UP;
            }
        }

        $halfModulus = bcdiv($modulus, 2);
        if ($roundingRule === Round::NORMAL) {
            if (bccomp($reminder, $halfModulus) === ComparisonResult::LESSER) {
                $roundingRule = Round::DOWN;
            } else {
                $roundingRule = Round::UP;
            }
        } elseif ($this->isNegative()) {
            $roundingRule = $roundingRule === Round::UP ? Round::DOWN : Round::UP;
        }

        $rounded = bcsub($this->value, $reminder);
        if ($roundingRule === Round::UP) {
            return new self(bcadd($rounded, $modulus), $this->size, $this->precision);
        } else {
            return new self($rounded, $this->size, $this->precision);
        }
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $modulus
     * @return self
     */
    public function roundUpTo($modulus): self
    {
        return $this->roundTo($modulus, Round::UP);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $modulus
     * @return self
     */
    public function roundDownTo($modulus): self
    {
        return $this->roundTo($modulus, Round::DOWN);
    }

    private function clip(int $fractionDigits = 0): self
    {
        $sign = $this->isNegative() ? '-' : '';
        $value = ltrim(Str::toFirst($this->value, '.'), '-');
        if ($fractionDigits > 0 && strstr($this->value, '.') !== false) {
            $value .= '.' . substr(Str::fromFirst($this->value, '.'), 0, $fractionDigits);
        } elseif ($fractionDigits < 0) {
            if (strlen($value) > -$fractionDigits) {
                $value = substr($value, 0, $fractionDigits) . str_repeat('0', -$fractionDigits);
            } else {
                $value = '0';
            }
        }
        return new self($sign . $value, $this->size, $this->precision);
    }

    // comparators -----------------------------------------------------------------------------------------------------

    public static function compare(self $first, self $second): int
    {
        return bccomp($first->value, $second->value, max($first->precision, $second->precision) + 2);
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return bool
     */
    public function equals($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        return $this->value === $other->value;
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return bool
     */
    public function greaterThan($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        return self::compare($this, $other) === ComparisonResult::GREATER;
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return bool
     */
    public function greaterOrEqual($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        return self::compare($this, $other) !== ComparisonResult::LESSER;
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return bool
     */
    public function lessThan($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        return self::compare($this, $other) === ComparisonResult::LESSER;
    }

    /**
     * @param int|string|\Dogma\Math\Decimal\Decimal $other
     * @return bool
     */
    public function lessOrEqual($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        return self::compare($this, $other) !== ComparisonResult::GREATER;
    }

}
