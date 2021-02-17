<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Decimal;

use Dogma\Check;
use Dogma\ComparisonResult;
use Dogma\Dumpable;
use Dogma\Equalable;
use Dogma\InvalidTypeException;
use Dogma\Math\Fraction;
use Dogma\Math\NullableCalc;
use Dogma\Round;
use Dogma\ShouldNotHappenException;
use Dogma\Sign;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use Dogma\ValueOutOfBoundsException;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_shift;
use function array_values;
use function bcadd;
use function bccomp;
use function bcdiv;
use function bcmod;
use function bcmul;
use function bcpow;
use function bcsqrt;
use function bcsub;
use function is_int;
use function is_string;
use function ltrim;
use function max;
use function rd;
use function rl;
use function rtrim;
use function str_repeat;
use function str_replace;
use function strlen;
use function strpos;
use function substr;

/**
 * Safe decimal numbers implemented using bcmath.
 *
 * Bcmath scale is calculated so there is no precision loss in basic operations; with the exception of power() and root().
 * If the arbitrary precise result won't fit into the configured precision, a ValueOutOfBoundsException is thrown.
 */
class Decimal implements Equalable, Dumpable
{
    use StrictBehaviorMixin;

    public const DEFAULT_PRECISION = 15;

    /** @var string */
    private $value;

    /** @var int */
    private $size;

    /** @var int */
    private $precision;

    /** @var bool */
    private $int;

    /**
     * @param string|int $value
     * @param int|null $size total number of digits
     * @param int|null $precision number of digits after decimal point
     */
    public function __construct($value, ?int $size = null, ?int $precision = null)
    {
        if (is_int($value)) {
            $value = (string) $value;
        } elseif (!is_string($value)) {
            throw new InvalidTypeException('string|int', $value);
        }

        if ($precision === null) {
            $precision = self::DEFAULT_PRECISION;
        }

        [$sign, $int, $fraction] = self::parse($value);

        self::checkSize($size, $precision);

        $this->value = $sign . $int . ($fraction ? '.' . $fraction : '');

        if (($size !== null && strlen($int) > ($size - $precision)) || strlen($fraction) > $precision) {
            $class = self::class;

            throw new ValueOutOfBoundsException($this->value, $size ? "$class($size,$precision)" : "$class(?,$precision)");
        }

        $this->size = $size;
        $this->precision = $precision;
        $this->int = !$fraction;
    }

    public static function checkSize(?int $size, int $precision): void
    {
        if ($size !== null && $size < 1) {
            throw new ValueOutOfBoundsException($size, "positive int");
        } elseif ($size !== null && $size < $precision) {
            throw new ValueOutOfBoundsException($size, "positive int greater or same as precision ($precision)");
        } elseif ($precision < 0) {
            throw new ValueOutOfBoundsException($precision, "positive int or zero");
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
        $int = ltrim($int, '0');
        if ($int === '') {
            $int = '0';
        }

        return [$sign, $int, $fraction];
    }

    public function __toString(): string
    {
        return $this->size ? "Decimal({$this->size},{$this->precision}): {$this->value}" : "Decimal(?,{$this->precision}): {$this->value}";
    }

    public function dump(): string
    {
        return $this->size ? "Decimal({$this->size},{$this->precision}): {$this->value}" : "Decimal(?,{$this->precision}): {$this->value}";
    }

    // getters ---------------------------------------------------------------------------------------------------------

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function getPrecision(): int
    {
        return $this->precision;
    }

    public function getMaxValue(): ?self
    {
        if ($this->size === null) {
            return null;
        }

        $value = str_repeat('9', $this->size - $this->precision) . ($this->precision ? '.' . str_repeat('9', $this->precision) : '');

        return new self($value, $this->size, $this->precision);
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

    public function isInt(): bool
    {
        return $this->int;
    }

    public function getIntValue(): int
    {
        if (!$this->int) {
            throw new ShouldNotHappenException("Value of Decimal ($this->value) is not integer.");
        }

        return (int) $this->value;
    }

    /**
     * @param int|string|self $root
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
        } catch (ValueOutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * @param int|string|self $other
     * @return bool
     */
    public function isDivisibleBy($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }
        try {
            return $this->divide($other)->isInt();
        } catch (ValueOutOfBoundsException $e) {
            return false;
        }
    }

    // todo: will fail on precision > 19 on 64bit or precision > 10 on 32bit
    public function toFraction(): Fraction
    {
        if ($this->int) {
            return new Fraction($this->getIntValue(), 1);
        } else {
            $exponent = $this->getCurrentPrecision();
            $numerator = $this->setSize(NullableCalc::add($this->size, $exponent))->multiply(10 ** $exponent);

            return Fraction::simplified($numerator->getIntValue(), 10 ** $exponent);
        }
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
            throw new InvalidTypeException('string|int', $value);
        }

        return new self($value, $this->getSize(), $this->getPrecision());
    }

    public function setSize(?int $size, ?int $precision = null): self
    {
        return new self($this->value, $size, $precision ?? $this->precision);
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
        if ($this->isZero()) {
            return $this;
        } elseif ($this->value[0] === '-') {
            return new self(substr($this->value, 1), $this->size, $this->precision);
        } else {
            return new self('-' . $this->value, $this->size, $this->precision);
        }
    }

    /**
     * @param int|string|self $other
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
     * @param int|string|self $other
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
     * @param int|string|self $multiplier
     * @param int|null $targetPrecision
     * @return self
     */
    public function multiply($multiplier, ?int $targetPrecision = null): self
    {
        if (!$multiplier instanceof self) {
            $multiplier = new self($multiplier);
        }
        $value = bcmul($this->value, $multiplier->value, $targetPrecision ?? $this->precision + $multiplier->precision + 1);

        $size = $targetPrecision !== null ? NullableCalc::addAll($this->size, -$this->precision, $targetPrecision) : $this->size;

        return new self($value, $size, $targetPrecision ?? $this->precision);
    }

    /**
     * @param int|string|self $divisor
     * @param int|null $targetPrecision
     * @return self
     */
    public function divide($divisor, ?int $targetPrecision = null): self
    {
        if (!$divisor instanceof self) {
            $divisor = new self($divisor);
        }
        $value = bcdiv($this->value, $divisor->value, $targetPrecision ?? $this->precision + $divisor->precision + 1);

        $size = $targetPrecision !== null ? NullableCalc::addAll($this->size, -$this->precision, $targetPrecision) : $this->size;

        return new self($value, $size, $targetPrecision ?? $this->precision);
    }

    /**
     * Returns result rounded to given precision and reminder over that precision.
     * @param int|string|self $divisor
     * @return self[]
     */
    public function divideWithReminder($divisor): array
    {
        if (!$divisor instanceof self) {
            $divisor = new self($divisor);
        }
        $int = (new self(bcdiv($this->value, $divisor->value, 6), $this->size * 3, 6))->clip();
        $divisor = $divisor->setSize(NullableCalc::add($divisor->getSize(), $int->getCurrentSize()));
        $reminder = $this->subtract($divisor->multiply($int));

        return [$int->setSize($this->size, $this->precision), $reminder];
    }

    /**
     * Returns remainder after division computed with symmetrical algorithm.
     * If no precision is given, returned value can have higher precision than left operand value.
     * @param int|string|self $divisor
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
     * @param int|string|self $modulus
     * @return self
     */
    public function integerModulo($modulus): self
    {
        if (!$modulus instanceof self) {
            $modulus = new self($modulus);
        }
        // todo: does not work in HHVM!
        $value = bcmod($this->value, $modulus->value);
        // strange behavior in PHP 7.2+
        if ($value === '-0') {
            $value = '0';
        }

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|self $nth
     * @return self
     */
    public function power($nth): self
    {
        if (!$nth instanceof self) {
            $nth = new self($nth);
        }
        if (!$nth->isInt()) {
            throw new DecimalArithmeticException("Only integer powers are supported. Cannot power by {$nth->getValue()}.", $this, $nth);
        }
        $value = bcpow($this->value, $nth->value, $this->precision * 2);

        return new self($value, $this->size, $this->precision);
    }

    /**
     * @param int|string|self $nth
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
            // todo: does not work actually
            $value = bcpow($this->value, bcdiv('1', $nth->value, $this->precision * 2), $this->precision * 2);
        }

        return new self($value, $this->size, $this->precision);
    }

    public function sqrt(): self
    {
        return new self(bcsqrt($this->value, $this->precision * 2), $this->size, $this->precision);
    }

    /**
     * @param int|int[]|string[]|self[] $parts count of shares or share ratios
     * @return self[]
     */
    public function distribute($parts): array
    {
        rf();
        Check::types($parts, [Type::INT, Type::PHP_ARRAY]);
        if (is_int($parts)) {
            $parts = array_fill(0, $parts, new self('1'));
        } else {
            foreach ($parts as $i => $part) {
                if (!$part instanceof self) {
                    $parts[$i] = new self($part);
                }
            }
            $parts = array_values($parts);
        }


        $total = self::sum($parts);
        if ($this->equals($total)) {
            return $parts;
        }

        $correction = (new Decimal(1))->divide((new Decimal(10))->power($this->precision));
        $idealAmounts = [];
        $roundedAmounts = [];
        $roundingErrors = [];
        $relativeRoundingErrors = [];
        foreach ($parts as $amount) {
            $idealAmount = $amount->divide($total, $this->precision * 3)->multiply($this, $this->precision * 3);
            $roundedAmount = $idealAmount->roundDown($this->precision);
            $idealAmounts[] = $idealAmount;
            $roundedAmounts[] = $roundedAmount;
            $roundingErrors[] = $idealAmount->subtract($roundedAmount)->abs();
            $relativeRoundingErrors[] = $idealAmount->subtract($roundedAmount)->divide($idealAmount ?: 1, $this->precision * 3)->abs();
        }

        $total = self::sum($roundedAmounts);

        rd($idealAmounts);
        rd($roundedAmounts);
        rd($roundingErrors);
        rd($relativeRoundingErrors);
        rd($total);

        $n = 0;
        while (!$this->equals($total)) {
            rl('FIX');
            // select amount with largest total error
            $keys = array_keys($roundingErrors, max($roundingErrors), true);
            if (count($keys) > 1) {
                // select amount with largest relative error
                $errors = array_intersect_key($relativeRoundingErrors, array_flip($keys));
                $key = array_keys($errors, max($errors), true)[0];
            } else {
                $key = $keys[0];
            }
            rd($key);
            if ($key===0) {
                $key=2;
            }

            $idealAmount = $idealAmounts[$key];
            $roundedAmount = $roundedAmounts[$key];
            $correctionAmount = $idealAmount->greaterThan($roundedAmount) ? $correction : $correction->negate();
            $roundedAmount = $roundedAmount->add($correctionAmount);
            $roundedAmounts[$key] = $roundedAmount;
            $roundingErrors[$key] = $idealAmount->subtract($roundedAmount)->abs();
            $relativeRoundingErrors[$key] = $idealAmount->subtract($roundedAmount)->divide($idealAmount ?: 1, $this->precision * 3)->abs();

            $total = self::sum($roundedAmounts);

            rd($idealAmounts);
            rd($roundedAmounts);
            rd($roundingErrors);
            rd($relativeRoundingErrors);
            rd($total);

            $n++;
            if ($n > 5) {
                exit;
            }
        }

        return $roundedAmounts;
    }

    // array operators -------------------------------------------------------------------------------------------------

    /**
     * @param int[]|string[]|Decimal[] $decimals
     * @return self
     */
    public static function sum(array $decimals): ?self
    {
        if ($decimals === []) {
            return null;
        }

        $result = array_shift($decimals);
        if (!$result instanceof self) {
            $result = new self($result);
        }
        foreach ($decimals as $decimal) {
            $result = $result->add($decimal);
        }

        return $result;
    }

    public static function product(array $decimals): ?self
    {
        if ($decimals === []) {
            return null;
        }

        $result = array_shift($decimals);
        if (!$result instanceof self) {
            $result = new self($result);
        }
        foreach ($decimals as $decimal) {
            $result = $result->multiply($decimal);
        }

        return $result;
    }

    public static function min(array $decimals): ?self
    {
        $result = null;
        foreach ($decimals as $decimal) {
            if ($result === null) {
                $result = $decimal instanceof self ? $decimal : new Decimal($decimal);
            } elseif ($result->greaterThan($decimal)) {
                $result = $decimal instanceof self ? $decimal : new Decimal($decimal);
            }
        }

        return $result;
    }

    public static function max(array $decimals): ?self
    {
        $result = null;
        foreach ($decimals as $decimal) {
            if ($result === null) {
                $result = $decimal instanceof self ? $decimal : new Decimal($decimal);
            } elseif ($result->lessThan($decimal)) {
                $result = $decimal instanceof self ? $decimal : new Decimal($decimal);
            }
        }

        return $result;
    }

    // rounding --------------------------------------------------------------------------------------------------------

    public function round(int $precision, int $roundingRule = Round::NORMAL): self
    {
        $modulus = new self(bcpow('10', '-' . $precision, $precision), $this->size, $precision);

        return $this->roundTo($modulus, $roundingRule);
    }

    public function roundUp(int $precision): self
    {
        return $this->round($precision, Round::UP);
    }

    public function roundDown(int $precision): self
    {
        return $this->round($precision, Round::DOWN);
    }

    /**
     * @param int|string|Decimal $modulus
     * @param int $roundingRule
     * @return self
     */
    public function roundTo($modulus, int $roundingRule = Round::NORMAL): self
    {
        if (!$modulus instanceof self) {
            $modulus = new self($modulus);
        }

        $reminder = bcmod($this->value, $modulus->value, $this->precision);

        if (strtr($reminder, '0.', '') === '') {
            return $this;
        }

        if ($this->isNegative()) {
            $modulus = $modulus->negate();
        }

        $halfModulus = bcdiv($modulus->value, '2', $this->precision + 1);
        if ($this->isNegative()) {
            if ($roundingRule === Round::UP) {
                $add = false;
            } elseif ($roundingRule === Round::DOWN) {
                $add = true;
            } elseif ($roundingRule === Round::TOWARDS_ZERO) {
                $add = false;
            } elseif ($roundingRule === Round::AWAY_FROM_ZERO) {
                $add = true;
            } elseif (bccomp($reminder, $halfModulus, $this->precision + 1) === ComparisonResult::LESSER) {
                $add = true;
            } else {
                $add = false;
            }
        } else {
            if ($roundingRule === Round::UP) {
                $add = true;
            } elseif ($roundingRule === Round::DOWN) {
                $add = false;
            } elseif ($roundingRule === Round::TOWARDS_ZERO) {
                $add = false;
            } elseif ($roundingRule === Round::AWAY_FROM_ZERO) {
                $add = true;
            } elseif (bccomp($reminder, $halfModulus, $this->precision + 1) === ComparisonResult::LESSER) {
                $add = false;
            } else {
                $add = true;
            }
        }

        $rounded = bcsub($this->value, $reminder, $this->precision);

        return $add
            ? new self(bcadd($rounded, $modulus->value, $this->precision), $this->size, $this->precision)
            : new self($rounded, $this->size, $this->precision);
    }

    /**
     * @param int|string|Decimal $modulus
     * @return self
     */
    public function roundUpTo($modulus): self
    {
        return $this->roundTo($modulus, Round::UP);
    }

    /**
     * @param int|string|Decimal $modulus
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
        if ($fractionDigits > 0 && strpos($this->value, '.') !== false) {
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
     * @param Decimal $other
     * @return bool
     */
    public function equals(Equalable $other): bool
    {
        Check::instance($other, self::class);

        return $this->value === $other->value;
    }

    /**
     * @param int|string|Decimal $other
     * @return bool
     */
    public function equalsValue($other): bool
    {
        if (!$other instanceof self) {
            $other = new self($other);
        }

        return $this->value === $other->value;
    }

    /**
     * @param int|string|Decimal $other
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
     * @param int|string|Decimal $other
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
     * @param int|string|Decimal $other
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
     * @param int|string|Decimal $other
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
