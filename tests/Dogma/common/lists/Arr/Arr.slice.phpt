<?php

namespace Dogma\Tests\ImmutableArray;

use Dogma\Arr;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';

$array = [1, 2, 3, 4];
$empty = [];

$f = function ($v) {
    return $v < 3;
};

// head()
Assert::same(Arr::head($array), 1);
Assert::null(Arr::head($empty));

// first()
Assert::same(Arr::first($array), 1);
Assert::null(Arr::first($empty));

// last()
Assert::same(Arr::last($array), 4);
Assert::null(Arr::last($empty));

// init()
Assert::same(Arr::init($array), [1, 2, 3]);
Assert::same(Arr::init($empty), []);

// tail()
Assert::same(Arr::tail($array), [1 => 2, 3, 4]);
Assert::same(Arr::tail($empty), []);

// inits()
Assert::same(Arr::inits($array), [[1, 2, 3, 4], [1, 2, 3], [1, 2], [1], []]);
Assert::same(Arr::inits($empty), [[]]);

// tails()
Assert::same(Arr::tails($array), [[1, 2, 3, 4], [1 => 2, 3, 4], [2 => 3, 4], [3 => 4], []]);
Assert::same(Arr::tails($empty), [[]]);

// headTail()
/** @var \Dogma\ImmutableArray $tail */
list($head, $tail) = Arr::headTail($array);
Assert::same($head, 1);
Assert::same($tail, [1 => 2, 3, 4]);
list($head, $tail) = Arr::headTail($empty);
Assert::null($head);
Assert::same($tail, []);

// initLast()
/** @var \Dogma\ImmutableArray $init */
list($init, $last) = Arr::initLast($array);
Assert::same($init, [1, 2, 3]);
Assert::same($last, 4);
list($init, $last) = Arr::initLast($empty);
Assert::same($init, []);
Assert::null($last);

// slice()
Assert::same(Arr::slice($array, 1), [1 => 2, 3, 4]);
Assert::same(Arr::slice($array, 1, 2), [1 => 2, 3]);
Assert::same(Arr::slice($array, 10), []);
Assert::same(Arr::slice($empty, 1), []);

// chunks()
Assert::same(Arr::chunks($array, 2), [[1, 2], [2 => 3, 4]]);
Assert::same(Arr::chunks($empty, 2), []);

// sliding()
Assert::same(Arr::sliding($array, 2), [[1, 2], [1 => 2, 3], [2 => 3, 4]]);
Assert::same(Arr::sliding($array, 3), [[1, 2, 3], [1 => 2, 3, 4]]);
Assert::same(Arr::sliding($array, 2, 2), [[1, 2], [2 => 3, 4]]);
Assert::same(Arr::sliding($empty, 2), []);

// drop()
Assert::same(Arr::drop($array, 2), [2 => 3, 4]);
Assert::same(Arr::drop($empty, 2), []);

// dropRight()
Assert::same(Arr::dropRight($array, 2), [1, 2]);
Assert::same(Arr::dropRight($empty, 2), []);

// dropWhile()
Assert::same(Arr::dropWhile($array, $f), [2 => 3, 4]);
Assert::same(Arr::dropWhile($empty, $f), []);

// padTo()
Assert::same(Arr::padTo($array, 7, 6), [1, 2, 3, 4, 6, 6, 6]);
Assert::same(Arr::padTo($empty, 3, 6), [6, 6, 6]);

// span()
list($l, $r) = Arr::span($array, $f);
Assert::same($l, [1, 2]);
Assert::same($r, [2 => 3, 4]);
list($l, $r) = Arr::span($empty, $f);
Assert::same($l, []);
Assert::same($r, []);

// take()
Assert::same(Arr::take($array, 2), [1, 2]);
Assert::same(Arr::take($empty, 2), []);

// takeRight()
Assert::same(Arr::takeRight($array, 2), [2 => 3, 4]);
Assert::same(Arr::takeRight($empty, 2), []);

// takeWhile()
Assert::same(Arr::takeWhile($array, $f), [1, 2]);
Assert::same(Arr::takeWhile($empty, $f), []);