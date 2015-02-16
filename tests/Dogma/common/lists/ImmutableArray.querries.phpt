<?php

namespace Dogma\Tests\ImmutableArray;

use Dogma\ImmutableArray;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$array = new ImmutableArray([1, 2, 3, 2, 4]);
$empty = new ImmutableArray([]);

// isEmpty()
Assert::true($empty->isEmpty());
Assert::false($array->isEmpty());

// isNotEmpty()
Assert::true($array->isNotEmpty());
Assert::false($empty->isNotEmpty());

// contains()
Assert::true($array->contains(2));
Assert::false($array->contains(5));

// indexOf()
Assert::null($array->indexOf(5));
Assert::same($array->indexOf(2), 1);
Assert::same($array->indexOf(2, 2), 3);

// indexesOf()
Assert::same($array->indexesOf(5)->toArray(), []);
Assert::same($array->indexesOf(2)->toArray(), [1, 3]);

// lastIndexOf()
Assert::null($array->lastIndexOf(5));
Assert::same($array->lastIndexOf(2), 3);
Assert::same($array->lastIndexOf(2, 2), 1);

// indexWhere()
Assert::null($array->indexWhere(function () {
    return false;
}));
Assert::same($array->indexWhere(function ($v) {
    return $v === 2;
}), 1);
Assert::same($array->indexWhere(function ($v) {
    return $v === 2;
}, 2), 3);

// lastIndexWhere()
Assert::null($array->lastIndexWhere(function () {
    return false;
}));
Assert::same($array->lastIndexWhere(function ($v) {
    return $v === 2;
}), 3);
Assert::same($array->lastIndexWhere(function ($v) {
    return $v === 2;
}, 2), 1);

// containsKey()
Assert::false($array->containsKey(5));
Assert::true($array->containsKey(2));

// exists()
Assert::false($array->exists(function ($v) {
    return $v > 5;
}));
Assert::true($array->exists(function ($v) {
    return $v > 1;
}));

// forAll()
Assert::false($array->forAll(function ($v) {
    return $v > 1;
}));
Assert::true($array->forAll(function ($v) {
    return $v < 5;
}));

// find()
Assert::null($array->find(function ($v) {
    return $v * $v === 25;
}));
Assert::same($array->find(function ($v) {
    return $v * $v === 4;
}), 2);

// prefixLength()
Assert::same((new ImmutableArray([2, 2, 2, 1]))->prefixLength(function ($v) {
    return $v === 2;
}), 3);

// segmentLength()
Assert::same((new ImmutableArray([2, 2, 2, 1]))->segmentLength(function ($v) {
    return $v === 2;
}, 1), 2);
