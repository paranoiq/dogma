<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use Dogma\Check;
use Dogma\InvalidTypeException;
use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Tuple;
use Dogma\Type;
use Dogma\ValueOutOfRangeException;

require_once __DIR__ . '/../../bootstrap.php';

// negative zero
$negativeZero = -(0.0);
Check::float($negativeZero);
Assert::same((string) $negativeZero, '0');

$negativeZero = -0.0;
Check::string($negativeZero);
Assert::same($negativeZero, '0');

// nullables
$null = null;

Check::nullableType($null, Type::BOOL);
Check::nullableBool($null);
Check::nullableInt($null);
Check::nullableFloat($null);
Check::nullableString($null);
Check::nullableObject($null);

$array = ['a' => 1, 'b' => 2, 'c' => 3];
$vector = [1, 2, 3];
$mixed = [1, 2, 'a', 'b'];

// type()
Check::type($vector, 'array<int>');
Assert::exception(function () use ($mixed) {
    Check::itemsOfType($mixed, Type::INT);
}, InvalidTypeException::class);

// itemsOfType()
Check::itemsOfType($array, Type::INT);
Assert::exception(function () use ($mixed) {
    Check::itemsOfType($mixed, Type::INT);
}, InvalidTypeException::class);

// itemsOfTypes()
Check::itemsOfTypes($mixed, [Type::INT, Type::STRING]);
Assert::exception(function () use ($mixed) {
    Check::itemsOfTypes($mixed, [Type::INT, Type::FLOAT]);
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
Check::array($array);
Assert::exception(function () use ($null) {
    Check::array($null);
}, InvalidTypeException::class);

// plainArray()
Check::plainArray($vector);
Assert::exception(function () use ($array) {
    Check::plainArray($array);
}, InvalidTypeException::class);

// tuple()
Check::tuple(new Tuple(123, 'abc'), [Type::INT, Type::STRING]);
Assert::exception(function () {
    Check::tuple(new Tuple(123, 'abc', 789), [Type::INT, Type::STRING]);
}, ValueOutOfRangeException::class);
Assert::exception(function () {
    Check::tuple(new Tuple(123), [Type::INT, Type::STRING]);
}, ValueOutOfRangeException::class);
Assert::exception(function () {
    Check::tuple(new Tuple('abc', 123), [Type::INT, Type::STRING]);
}, InvalidTypeException::class);
Assert::exception(function () use ($array) {
    Check::tuple($array, [Type::INT, Type::STRING]);
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
$big = 100;

Assert::exception(function () use ($small) {
    Check::int($small, 0);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($big) {
    Check::int($big, 0, 10);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($small) {
    Check::float($small, 0.0);
}, ValueOutOfRangeException::class);

Assert::exception(function () use ($big) {
    Check::float($big, 0.0, 10.0);
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

    public function getIterator(): array
    {
        return [];
    }

}

class TestNonTraversable
{
    // pass
}

// isTraversable()
Assert::true(Check::isIterable(array()));
Assert::true(Check::isIterable(new \stdClass()));
Assert::true(Check::isIterable(new TestTraversable()));
Assert::false(Check::isIterable(new TestNonTraversable()));

// isPlainArray()
Assert::true(Check::isPlainArray([]));
Assert::true(Check::isPlainArray([1, 2, 3]));
Assert::false(Check::isPlainArray([1 => 1, 2, 3]));
Assert::false(Check::isPlainArray(['a' => 1, 2, 3]));
Assert::false(Check::isPlainArray(['a' => 1, 'b' => 2, 'c' => 3]));
