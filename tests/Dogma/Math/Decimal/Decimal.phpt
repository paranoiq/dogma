<?php

namespace Dogma\Tests\Math\Decimal;

use Dogma\Math\Decimal\Decimal;
use Dogma\Sign;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$values = function (array $decimals) {
    return array_map(function (Decimal $decimal) {
        return $decimal->getValue();
    }, $decimals);
};

Assert::exception(function () {
    new Decimal('1', 66, 0);
}, \Dogma\ValueOutOfBoundsException::class);

Assert::exception(function () {
    new Decimal(str_repeat('9', 66), 65, 0);
}, \Dogma\ValueOutOfBoundsException::class);

// 112.5 = 25 * 4.5; 112 = 7 * 2^4
$positive = new Decimal('112.5', 6, 2);
$negative = new Decimal('-112.5', 6, 2);
$zero = new Decimal('0', 6, 2);
$long = new Decimal('1111.1111', 8, 4);

// __toString()
Assert::same((string) $positive, 'Decimal(6,2): 112.5');
Assert::same((string) $negative, 'Decimal(6,2): -112.5');
Assert::same((string) $zero, 'Decimal(6,2): 0');

// getValue()
Assert::same((new Decimal(str_repeat('9', 65), 65, 0))->getValue(), str_repeat('9', 65));

// getSize()
Assert::same($positive->getSize(), 6);

// getPrecision()
Assert::same($positive->getPrecision(), 2);

// getMaxValue()
Assert::same($positive->getMaxValue()->getValue(), '9999.99');

// getCurrentSize()
Assert::same($positive->getCurrentSize(), 4);
Assert::same($negative->getCurrentSize(), 4);
Assert::same($zero->getCurrentSize(), 1);

// getCurrentPrecision()
Assert::same($positive->getCurrentPrecision(), 1);
Assert::same($negative->getCurrentPrecision(), 1);
Assert::same($zero->getCurrentPrecision(), 0);

// getValue()
Assert::same($positive->getValue(), '112.5');
Assert::same($negative->getValue(), '-112.5');
Assert::same($zero->getValue(), '0');

// getSign()
Assert::same($positive->getSign(), Sign::POSITIVE);
Assert::same($negative->getSign(), Sign::NEGATIVE);
Assert::same($zero->getSign(), Sign::NEUTRAL);

// isPositive()
Assert::same($positive->isPositive(), true);
Assert::same($negative->isPositive(), false);
Assert::same($zero->isPositive(), false);

// isNegative()
Assert::same($positive->isNegative(), false);
Assert::same($negative->isNegative(), true);
Assert::same($zero->isNegative(), false);

// isZero()

// isInteger()

// toFraction()
Assert::same((string) (new Decimal('1.25'))->toFraction(), 'Fraction: 5/4');
Assert::same((string) (new Decimal('1.28'))->toFraction(), 'Fraction: 32/25');




// abs()
Assert::same($positive->abs()->getValue(), $positive->getValue());
Assert::same($negative->abs()->getValue(), $positive->getValue());

// negate()
Assert::same($positive->negate()->getValue(), $negative->getValue());
Assert::same($negative->negate()->getValue(), $positive->getValue());

// add()
Assert::same($positive->add($positive)->getValue(), '225');
Assert::same($positive->add($negative)->getValue(), '0');
Assert::exception(function () use ($positive, $long) {
    $positive->add($long);
}, \Dogma\ValueOutOfBoundsException::class);

// subtract()
Assert::same($positive->subtract($negative)->getValue(), '225');
Assert::same($positive->subtract($positive)->getValue(), '0');
Assert::exception(function () use ($positive, $long) {
    $positive->subtract($long);
}, \Dogma\ValueOutOfBoundsException::class);

// multiply()
Assert::same((new Decimal('25', 6, 2))->multiply('4.5')->getValue(), $positive->getValue());
// too big
Assert::exception(function () use ($positive, $long) {
    $positive->multiply($positive);
}, \Dogma\ValueOutOfBoundsException::class);
// not enough precision
Assert::exception(function () use ($positive, $long) {
    $positive->multiply($long);
}, \Dogma\ValueOutOfBoundsException::class);

// divide()
Assert::same($positive->divide('3')->getValue(), '37.5');
Assert::same($negative->divide('3')->getValue(), '-37.5');
Assert::same($positive->divide('4.5')->getValue(), '25');
Assert::same($negative->divide('4.5')->getValue(), '-25');
// irrational
Assert::exception(function () use ($positive) {
    $positive->divide('7');
}, \Dogma\ValueOutOfBoundsException::class);
Assert::exception(function () use ($positive) {
    $positive->divide('11');
}, \Dogma\ValueOutOfBoundsException::class);

// divideWithReminder()
Assert::same($values($positive->divideWithReminder('4.5')), ['25', '0']);
Assert::same($values($negative->divideWithReminder('4.5')), ['-25', '0']);
Assert::same($values($positive->divideWithReminder('7')), ['16', '0.5']);
Assert::same($values($negative->divideWithReminder('7')), ['-16', '-0.5']);
Assert::same($values($positive->divideWithReminder('11')), ['10', '2.5']);
Assert::same($values($negative->divideWithReminder('11')), ['-10', '-2.5']);

// reminder()
Assert::same($positive->reminder('4.5')->getValue(), '0');
Assert::same($negative->reminder('4.5')->getValue(), '0');
Assert::same($positive->reminder('7')->getValue(), '0.5');
Assert::same($negative->reminder('7')->getValue(), '-0.5');
Assert::same($positive->reminder('11')->getValue(), '2.5');
Assert::same($negative->reminder('11')->getValue(), '-2.5');

// integerModulo()
Assert::same($positive->integerModulo('4.5')->getValue(), '0');
Assert::same($negative->integerModulo('4.5')->getValue(), '0');
Assert::same($positive->integerModulo('7')->getValue(), '0');
Assert::same($negative->integerModulo('7')->getValue(), '0');
Assert::same($positive->integerModulo('11')->getValue(), '2');
Assert::same($negative->integerModulo('11')->getValue(), '-2');

// power()
Assert::same((new Decimal('4', 6, 2))->power('2')->getValue(), '16');
Assert::same((new Decimal('2', 6, 2))->power('-2')->getValue(), '0.25');
Assert::same((new Decimal('-1.5', 6, 3))->power('3')->getValue(), '-3.375');
// too big
Assert::exception(function () use ($positive, $long) {
    (new Decimal('16', 6, 2))->power('4');
}, \Dogma\ValueOutOfBoundsException::class);
// not enough precision
Assert::exception(function () use ($positive, $long) {
    (new Decimal('-1.5', 6, 2))->power('3');
}, \Dogma\ValueOutOfBoundsException::class);

// bcmath does not support power with fraction
// this wont work:
//    Assert::same((new Decimal('16', 6, 2))->power('2.5')->getValue(), '1024');
// this neither:
//    Assert::exception(function () use ($positive, $long) {
//        (new Decimal('8', 6, 2))->power('2.5');
//    }, \Dogma\ValueOutOfBoundsException::class);
Assert::exception(function () use ($positive, $long) {
    (new Decimal('16', 6, 2))->power('2.5');
}, \Dogma\Math\Decimal\DecimalArithmeticException::class);

// root()
Assert::same((new Decimal('16', 6, 2))->root('2')->getValue(), '4');
Assert::same((new Decimal('0.25', 6, 3))->root('2')->getValue(), '0.5');
// irrational
Assert::exception(function () use ($positive, $long) {
    (new Decimal('-1.5', 6, 2))->power('3');
}, \Dogma\ValueOutOfBoundsException::class);

/// bcmath does not support power with fraction and this cannot be enumerated anyway (n^0.333...)
//Assert::same((new Decimal('-3.375', 6, 3))->root('3')->getValue(), '-1.5');

// round()

// roundTo()


// compare()

// equals()

// greaterThan()

// greaterOrEqual()

// lessThan()

// lessOrEqual()
