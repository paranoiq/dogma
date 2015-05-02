<?php

namespace Dogma\Tests\ChunkIterator;

use Dogma\ChunkIterator;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$array = range(1, 35);
$empty = [];

$result = [];
foreach (new ChunkIterator($array, 10) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [range(1, 10), range(11, 20), range(21, 30), range(31, 35)]);

$result = [];
foreach (new ChunkIterator($empty, 10) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, []);

Assert::throws(function () {
    new ChunkIterator([], 0);
}, \Dogma\ValueOutOfRangeException::class);
