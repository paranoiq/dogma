<?php

namespace Dogma\Tests\Type;

use Dogma\Type;
use StdClass;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';



$null = null;

// arrayType
$array = [1, 2, 3];
Type::phpArray($array);

Assert::exception(function () use ($null) {
    Type::phpArray($null);
}, \Dogma\InvalidTypeException::class);


// vector
$vector = [1, 2, 3];
Type::vector($vector);

Assert::exception(function () use ($null) {
    Type::vector($null);
}, \Dogma\InvalidTypeException::class);

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
Assert::true(Type::isTraversable(array()));
Assert::true(Type::isTraversable(new \StdClass()));
Assert::true(Type::isTraversable(new TestTraversable()));
Assert::false(Type::isTraversable(new TestNonTraversable()));

// isVector()
Assert::true(Type::isVector([]));
Assert::true(Type::isVector([1, 2, 3]));
Assert::false(Type::isVector([1 => 1, 2, 3]));
Assert::false(Type::isVector(['a' => 1, 2, 3]));
Assert::false(Type::isVector(['a' => 1, 'b' => 2, 'c' => 3]));
