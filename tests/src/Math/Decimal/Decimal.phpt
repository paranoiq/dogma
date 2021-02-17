<?php declare(strict_types = 1);

namespace Dogma\Tests\Math\Decimal;

use Dogma\Math\Decimal\Decimal;
use Dogma\Math\Decimal\DecimalArithmeticException;
use Dogma\Round;
use Dogma\ShouldNotHappenException;
use Dogma\Sign;
use Dogma\Tester\Assert;
use Dogma\ValueOutOfBoundsException;
use function __construct;
use function array_map;
use function count;
use function str_repeat;

require_once __DIR__ . '/../../bootstrap.php';

$values = static function (array $decimals) {
    return array_map(static function (Decimal $decimal) {
        return $decimal->getValue();
    }, $decimals);
};
$d = static function (string $n) {
    return new Decimal($n);
};


// 112.5 = 25 * 4.5; 112 = 7 * 2^4
$positive = new Decimal('112.5', 6, 2);
$negative = new Decimal('-112.5', 6, 2);
$zero = new Decimal('0', 6, 2);
$long = new Decimal('1111.1111', 8, 4);


__construct:
Assert::exception(static function () {
    new Decimal('999999', 5, 0);
}, ValueOutOfBoundsException::class);

Assert::exception(static function () {
    new Decimal('9999.9', 4, 1);
}, ValueOutOfBoundsException::class);

Assert::exception(static function () {
    new Decimal('9', 5, 6);
}, ValueOutOfBoundsException::class);

Assert::exception(static function () {
    new Decimal('9', 0, 0);
}, ValueOutOfBoundsException::class);

Assert::exception(static function () {
    new Decimal('9', 0, -1);
}, ValueOutOfBoundsException::class);

Assert::exception(static function () {
    new Decimal('99999999999.9', null, 0);
}, ValueOutOfBoundsException::class);

new Decimal('99999999999.9', null, 1);


__toString:
Assert::same((string) $positive, 'Decimal(6,2): 112.5');
Assert::same((string) $negative, 'Decimal(6,2): -112.5');
Assert::same((string) $zero, 'Decimal(6,2): 0');


getSize:
Assert::same($positive->getSize(), 6);


getPrecision:
Assert::same($positive->getPrecision(), 2);


getMaxValue:
Assert::same($positive->getMaxValue()->getValue(), '9999.99');


getCurrentSize:
Assert::same($positive->getCurrentSize(), 4);
Assert::same($negative->getCurrentSize(), 4);
Assert::same($zero->getCurrentSize(), 1);


getCurrentPrecision:
Assert::same($positive->getCurrentPrecision(), 1);
Assert::same($negative->getCurrentPrecision(), 1);
Assert::same($zero->getCurrentPrecision(), 0);


getValue:
Assert::same((new Decimal(str_repeat('9', 65), 65, 0))->getValue(), str_repeat('9', 65));
Assert::same($positive->getValue(), '112.5');
Assert::same($negative->getValue(), '-112.5');
Assert::same($zero->getValue(), '0');


getSign:
Assert::same($positive->getSign(), Sign::POSITIVE);
Assert::same($negative->getSign(), Sign::NEGATIVE);
Assert::same($zero->getSign(), Sign::NEUTRAL);


isPositive:
Assert::same($positive->isPositive(), true);
Assert::same($negative->isPositive(), false);
Assert::same($zero->isPositive(), false);


isNegative:
Assert::same($positive->isNegative(), false);
Assert::same($negative->isNegative(), true);
Assert::same($zero->isNegative(), false);


isZero:
Assert::true($zero->isZero());
Assert::false($positive->isZero());
Assert::false($negative->isZero());


isInt:
Assert::true($zero->isInt());
Assert::false($positive->isInt());
Assert::false($negative->isInt());


getIntValue:
Assert::same($zero->getIntValue(), 0);
Assert::exception(static function () use ($positive) {
    $positive->getIntValue();
}, ShouldNotHappenException::class);


isPowerOf:
Assert::false($zero->isPowerOf(2));
Assert::true((new Decimal(9))->isPowerOf(-3));
Assert::false((new Decimal(-9))->isPowerOf(-3));
Assert::true((new Decimal(27))->isPowerOf(3));
Assert::true((new Decimal(-27))->isPowerOf(-3));


isDivisibleBy:
Assert::true($zero->isDivisibleBy(751));
Assert::true((new Decimal(9))->isDivisibleBy(3));
Assert::true((new Decimal(9))->isDivisibleBy(-3));
Assert::false((new Decimal(9))->isDivisibleBy(4));
Assert::true((new Decimal(9))->isDivisibleBy('4.5'));


toFraction:
Assert::same((string) (new Decimal('1.25'))->toFraction(), 'Fraction: 5/4');
Assert::same((string) (new Decimal('1.28'))->toFraction(), 'Fraction: 32/25');


// operators -----------------------------------------------------------------------------------------------------------


abs:
Assert::same($zero->abs()->getValue(), $zero->getValue());
Assert::same($positive->abs()->getValue(), $positive->getValue());
Assert::same($negative->abs()->getValue(), $positive->getValue());


negate:
Assert::same($zero->negate()->getValue(), $zero->getValue());
Assert::same($positive->negate()->getValue(), $negative->getValue());
Assert::same($negative->negate()->getValue(), $positive->getValue());


add:
Assert::same($positive->add($positive)->getValue(), '225');
Assert::same($positive->add($negative)->getValue(), '0');
Assert::exception(function () use ($positive, $long) {
    $positive->add($long);
}, ValueOutOfBoundsException::class);


subtract:
Assert::same($positive->subtract($negative)->getValue(), '225');
Assert::same($positive->subtract($positive)->getValue(), '0');
Assert::exception(static function () use ($positive, $long) {
    $positive->subtract($long);
}, ValueOutOfBoundsException::class);


multiply:
Assert::same((new Decimal('25', 6, 2))->multiply('4.5')->getValue(), $positive->getValue());
// too big
Assert::exception(static function () use ($positive) {
    $positive->multiply($positive);
}, ValueOutOfBoundsException::class);
// not enough precision
Assert::exception(static function () use ($positive, $long) {
    $positive->multiply($long);
}, ValueOutOfBoundsException::class);


divide:
Assert::same($positive->divide('3')->getValue(), '37.5');
Assert::same($negative->divide('3')->getValue(), '-37.5');
Assert::same($positive->divide('4.5')->getValue(), '25');
Assert::same($negative->divide('4.5')->getValue(), '-25');
// irrational
Assert::exception(static function () use ($positive) {
    $positive->divide('7');
}, ValueOutOfBoundsException::class);
Assert::exception(static function () use ($positive) {
    $positive->divide('11');
}, ValueOutOfBoundsException::class);


divideWithReminder:
Assert::same($values($positive->divideWithReminder('4.5')), ['25', '0']);
Assert::same($values($negative->divideWithReminder('4.5')), ['-25', '0']);
Assert::same($values($positive->divideWithReminder('7')), ['16', '0.5']);
Assert::same($values($negative->divideWithReminder('7')), ['-16', '-0.5']);
Assert::same($values($positive->divideWithReminder('11')), ['10', '2.5']);
Assert::same($values($negative->divideWithReminder('11')), ['-10', '-2.5']);


reminder:
Assert::same($positive->reminder('4.5')->getValue(), '0');
Assert::same($negative->reminder('4.5')->getValue(), '0');
Assert::same($positive->reminder('7')->getValue(), '0.5');
Assert::same($negative->reminder('7')->getValue(), '-0.5');
Assert::same($positive->reminder('11')->getValue(), '2.5');
Assert::same($negative->reminder('11')->getValue(), '-2.5');


integerModulo:
Assert::same($positive->integerModulo('4.5')->getValue(), '0');
Assert::same($negative->integerModulo('4.5')->getValue(), '0');
Assert::same($positive->integerModulo('7')->getValue(), '0');
Assert::same($negative->integerModulo('7')->getValue(), '0');
Assert::same($positive->integerModulo('11')->getValue(), '2');
Assert::same($negative->integerModulo('11')->getValue(), '-2');


power:
Assert::same((new Decimal('4', 6, 2))->power('2')->getValue(), '16');
Assert::same((new Decimal('2', 6, 2))->power('-2')->getValue(), '0.25');
Assert::same((new Decimal('-1.5', 6, 3))->power('3')->getValue(), '-3.375');
// too big
Assert::exception(static function () {
    (new Decimal('16', 6, 2))->power('4');
}, ValueOutOfBoundsException::class);
// not enough precision
Assert::exception(static function () {
    (new Decimal('-1.5', 6, 2))->power('3');
}, ValueOutOfBoundsException::class);

// todo: bcmath does not support power with fraction
// this wont work:
//    Assert::same((new Decimal('16', 6, 2))->power('2.5')->getValue(), '1024');
// this neither:
//    Assert::exception(function () use ($positive, $long) {
//        (new Decimal('8', 6, 2))->power('2.5');
//    }, ValueOutOfBoundsException::class);
Assert::exception(static function () {
    (new Decimal('16', 6, 2))->power('2.5');
}, DecimalArithmeticException::class);


root:
Assert::same((new Decimal('16', 6, 2))->root('2')->getValue(), '4');
Assert::same((new Decimal('0.25', 6, 3))->root('2')->getValue(), '0.5');
// irrational
Assert::exception(static function () {
    (new Decimal('-1.5', 6, 2))->power('3');
}, ValueOutOfBoundsException::class);


// todo: bcmath does not support power with fraction and this cannot be enumerated anyway (n^0.333...)
//Assert::same((new Decimal('-3.375', 6, 3))->root('3')->getValue(), '-1.5');

sqrt:


sum:


product:


min:


max:


round:
roundUp:
roundDown:
Assert::same((new Decimal('1.2345'))->round(2)->getValue(), '1.23');
Assert::same((new Decimal('-1.2345'))->round(2)->getValue(), '-1.23');
Assert::same((new Decimal('1.2350'))->round(2)->getValue(), '1.24');
Assert::same((new Decimal('-1.2350'))->round(2)->getValue(), '-1.23');
Assert::same((new Decimal('1.2355'))->round(2)->getValue(), '1.24');
Assert::same((new Decimal('-1.2355'))->round(2)->getValue(), '-1.24');

Assert::same((new Decimal('1.2345'))->round(2, Round::UP)->getValue(), '1.24');
Assert::same((new Decimal('-1.2345'))->round(2, Round::UP)->getValue(), '-1.23');
Assert::same((new Decimal('1.2350'))->round(2, Round::UP)->getValue(), '1.24');
Assert::same((new Decimal('-1.2350'))->round(2, Round::UP)->getValue(), '-1.23');
Assert::same((new Decimal('1.2355'))->round(2, Round::UP)->getValue(), '1.24');
Assert::same((new Decimal('-1.2355'))->round(2, Round::UP)->getValue(), '-1.23');

Assert::same((new Decimal('1.2345'))->round(2, Round::DOWN)->getValue(), '1.23');
Assert::same((new Decimal('-1.2345'))->round(2, Round::DOWN)->getValue(), '-1.24');
Assert::same((new Decimal('1.2350'))->round(2, Round::DOWN)->getValue(), '1.23');
Assert::same((new Decimal('-1.2350'))->round(2, Round::DOWN)->getValue(), '-1.24');
Assert::same((new Decimal('1.2355'))->round(2, Round::DOWN)->getValue(), '1.23');
Assert::same((new Decimal('-1.2355'))->round(2, Round::DOWN)->getValue(), '-1.24');

Assert::same((new Decimal('1.2345'))->round(2, Round::AWAY_FROM_ZERO)->getValue(), '1.24');
Assert::same((new Decimal('-1.2345'))->round(2, Round::AWAY_FROM_ZERO)->getValue(), '-1.24');
Assert::same((new Decimal('1.2350'))->round(2, Round::AWAY_FROM_ZERO)->getValue(), '1.24');
Assert::same((new Decimal('-1.2350'))->round(2, Round::AWAY_FROM_ZERO)->getValue(), '-1.24');
Assert::same((new Decimal('1.2355'))->round(2, Round::AWAY_FROM_ZERO)->getValue(), '1.24');
Assert::same((new Decimal('-1.2355'))->round(2, Round::AWAY_FROM_ZERO)->getValue(), '-1.24');

Assert::same((new Decimal('1.2345'))->round(2, Round::TOWARDS_ZERO)->getValue(), '1.23');
Assert::same((new Decimal('-1.2345'))->round(2, Round::TOWARDS_ZERO)->getValue(), '-1.23');
Assert::same((new Decimal('1.2350'))->round(2, Round::TOWARDS_ZERO)->getValue(), '1.23');
Assert::same((new Decimal('-1.2350'))->round(2, Round::TOWARDS_ZERO)->getValue(), '-1.23');
Assert::same((new Decimal('1.2355'))->round(2, Round::TOWARDS_ZERO)->getValue(), '1.23');
Assert::same((new Decimal('-1.2355'))->round(2, Round::TOWARDS_ZERO)->getValue(), '-1.23');


roundTo:
roundUpTo:
roundDownTo:
Assert::same((new Decimal('1.124'))->roundTo('0.05')->getValue(), '1.1');
Assert::same((new Decimal('-1.124'))->roundTo('0.05')->getValue(), '-1.1');
Assert::same((new Decimal('1.125'))->roundTo('0.05')->getValue(), '1.15');
Assert::same((new Decimal('-1.125'))->roundTo('0.05')->getValue(), '-1.1');
Assert::same((new Decimal('1.126'))->roundTo('0.05')->getValue(), '1.15');
Assert::same((new Decimal('-1.126'))->roundTo('0.05')->getValue(), '-1.15');







compare:


equals:
Assert::true($zero->equals(new Decimal(0)));
Assert::true($zero->equals(new Decimal(0, 10, 3)));
Assert::true($positive->equals($positive->setSize(10)));
Assert::true($positive->equals($negative->negate()));
Assert::false($positive->equals($negative));


greaterThan:


greaterOrEqual:


lessThan:


lessOrEqual:


distribute:
$data = [
    // basics
    /*['7.00', [3, 4, 5], ['1.75', '2.33', '2.92']],
    ['8.00', [3, 4, 5], ['2.00', '2.67', '3.33']],
    ['9.00', [3, 4, 5], ['2.25', '3.00', '3.75']],*/
    // extremes
    ['11.70', [3, 4, 5], ['2.92', '3.90', '4.88']],
    ['11.77', [3, 4, 5], ['2.94', '3.92', '4.91']],
    ['11.89', [3, 4, 5], ['2.97', '3.96', '4.96']],
    ['11.93', [3, 4, 5], ['2.98', '3.98', '4.97']],
    ['11.98', [3, 4, 5], ['3.00', '3.99', '4.99']],
    ['11.99', [3, 4, 5], ['3.00', '4.00', '4.99']],///////
    ['12.01', [3, 4, 5], ['3.00', '4.00', '5.01']],
    ['12.02', [3, 4, 5], ['3.00', '4.01', '5.01']],
    ['12.07', [3, 4, 5], ['3.02', '4.02', '5.03']],
    ['12.11', [3, 4, 5], ['3.03', '4.04', '5.04']],
    ['12.23', [3, 4, 5], ['3.06', '4.08', '5.09']],
    ['12.30', [3, 4, 5], ['3.07', '4.10', '5.13']],
    // order stability
    ['11.70', [5, 4, 3], ['4.88', '3.90', '2.92']],
    ['12.30', [5, 4, 3], ['5.13', '4.10', '3.07']],
    // no change
    ['12.00', [3, 4, 5], ['3.00', '4.00', '5.00']],
    // zeroes
    ['7.00', [3, 4, 5, 0], ['1.75', '2.33', '2.92', '0.00']],
    ['0.00', [0, 0, 0, 0], ['0.00', '0.00', '0.00', '0.00']],
];
foreach ($data as $j => [$amount, $parts, $expected]) {
    $results = (new Decimal($amount, null, 2))->distribute($parts);
rd($j);
    Assert::same(count($results), count($expected));
    rd($results);
    rd($expected);
    foreach ($results as $i => $result) {
        Assert::equal($result, new Decimal($expected[$i]));
    }
}