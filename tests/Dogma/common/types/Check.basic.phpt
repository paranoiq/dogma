<?php

namespace Dogma\Tests\Type;

use Dogma\Check;
use Dogma\InvalidTypeException;
use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Tuple;
use Dogma\Type;
use Dogma\ValueOutOfRangeException;
use SplFixedArray;
use StdClass;

require_once __DIR__ . '/../../bootstrap.php';

// nullables
$null = null;

Check::nullableType($null, Type::BOOLEAN);
Check::nullableBoolean($null);
Check::nullableInteger($null);
Check::nullableNatural($null);
Check::nullableFloat($null);
Check::nullableString($null);
Check::nullableObject($null);

// natural
$positive = 1;
Check::natural($positive);

$negative = -1;
Assert::exception(function () use ($negative) {
    Check::natural($negative);
}, InvalidValueException::class);

$zero = 0;
Assert::exception(function () use ($zero) {
    Check::natural($zero);
}, InvalidValueException::class);

$array = ['a' => 1, 'b' => 2, 'c' => 3];
$vector = [1, 2, 3];
$mixed = [1, 2, 'a', 'b'];

// itemsOfType()
Check::itemsOfType($array, Type::INTEGER);
Assert::exception(function () use ($mixed) {
    Check::itemsOfType($mixed, Type::INTEGER);
}, InvalidTypeException::class);

// itemsOfTypes()
Check::itemsOfTypes($mixed, [Type::INTEGER, Type::STRING]);
Assert::exception(function () use ($mixed) {
    Check::itemsOfTypes($mixed, [Type::INTEGER, Type::FLOAT]);
}, InvalidTypeException::class);

// traversable()
Check::traversable($array);
Check::traversable($vector);
Check::traversable(new \SplFixedArray());
Check::traversable(new \stdClass());
Assert::exception(function () {
    Check::traversable(new \Exception());
}, InvalidTypeException::class);

// phpArray()
Check::phpArray($array);
Assert::exception(function () use ($null) {
    Check::phpArray($null);
}, InvalidTypeException::class);

// plainArray()
Check::plainArray($vector);
Assert::exception(function () use ($array) {
    Check::plainArray($array);
}, InvalidTypeException::class);

// tuple()
Check::tuple(new Tuple(123, 'abc'), [Type::INTEGER, Type::STRING]);
Assert::exception(function () {
    Check::tuple(new Tuple(123, 'abc', 789), [Type::INTEGER, Type::STRING]);
}, ValueOutOfRangeException::class);
Assert::exception(function () {
    Check::tuple(new Tuple(123), [Type::INTEGER, Type::STRING]);
}, ValueOutOfRangeException::class);
Assert::exception(function () {
    Check::tuple(new Tuple('abc', 123), [Type::INTEGER, Type::STRING]);
}, InvalidTypeException::class);
Assert::exception(function () use ($array) {
    Check::tuple($array, [Type::INTEGER, Type::STRING]);
}, InvalidTypeException::class);


// object()
Check::object(new \stdClass(), \stdClass::class);
Assert::exception(function () use ($array) {
    Check::object($array, \stdClass::class);
}, InvalidTypeException::class);

// className()
Check::className(\stdClass::class);
Assert::exception(function () {
    Check::className(Type::STRING);
}, InvalidValueException::class);

// typeName
Check::typeName(\stdClass::class);
Check::typeName(Type::STRING);
Assert::exception(function () {
    Check::typeName('asdf');
}, InvalidValueException::class);

// ranges
$small = -100;
$zero = 0;
$big = 100;

Assert::exception(function () use ($small) {
    Check::integer($small, 0);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($big) {
    Check::integer($big, 0, 10);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($small) {
    Check::natural($small);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($zero) {
    Check::natural($zero);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($small) {
    Check::float($small, 0);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($big) {
    Check::float($big, 0, 10);
}, ValueOutOfRangeException::class);

$short = 'abc';
$long = 'abcabcabc';

Assert::exception(function () use ($short) {
    Check::string($short, 5);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($long) {
    Check::string($long, 5, 6);
}, ValueOutOfRangeException::class);

// oneOf()
Check::oneOf($short);
Check::oneOf($short, $null);
Check::oneOf($null, $short);
Assert::exception(function () use ($short) {
    Check::oneOf($short, $short);
}, ValueOutOfRangeException::class);

class TestTraversable implements \IteratorAggregate
{

    public function getIterator()
    {
        return [];
    }

}

class TestNonTraversable
{
    // pass
}

// isTraversable()
Assert::true(Check::isTraversable(array()));
Assert::true(Check::isTraversable(new \stdClass()));
Assert::true(Check::isTraversable(new TestTraversable()));
Assert::false(Check::isTraversable(new TestNonTraversable()));

// isPlainArray()
Assert::true(Check::isPlainArray([]));
Assert::true(Check::isPlainArray([1, 2, 3]));
Assert::false(Check::isPlainArray([1 => 1, 2, 3]));
Assert::false(Check::isPlainArray(['a' => 1, 2, 3]));
Assert::false(Check::isPlainArray(['a' => 1, 'b' => 2, 'c' => 3]));
