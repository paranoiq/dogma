<?php declare(strict_types = 1);

namespace Dogma\Tests\CombineIterator;

use Dogma\RoundRobinIterator;
use Dogma\Tester\Assert;
use Dogma\UnevenIteratorSourcesException;

require_once __DIR__ . '/../../bootstrap.php';

$first = [1, 2, 3];
$second = [4, 5, 6];
$third = [7, 8, 9];
$result = [];
foreach (new RoundRobinIterator($first, $second, $third) as $k => $v) {
    $result[$k] = $v;
}
Assert::same($result, [1, 4, 7, 2, 5, 8, 3, 6, 9]);

Assert::exception(function () {
    $first = [1, 2];
    $second = [4, 5, 6];
    $third = [7, 8, 9];
    $result = [];
    foreach (new RoundRobinIterator($first, $second, $third) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(function () {
    $first = [1, 2, 3];
    $second = [4, 5, 6];
    $third = [7, 8];
    $result = [];
    foreach (new RoundRobinIterator($first, $second, $third) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(function () {
    $first = [];
    $second = [4, 5, 6];
    $third = [7, 8, 9];
    $result = [];
    foreach (new RoundRobinIterator($first, $second, $third) as $k => $v) {
        $result[$k] = $v;
    }
}, UnevenIteratorSourcesException::class);

Assert::exception(function () {
    $first = [1, 2, 3];
    $second = [4, 5, 6];
    $third = [];
    $result = [];
    foreach (new RoundRobinIterator($first, $second, $third) as $k => $v) {
        $result[$k] = $v;
    }
    Assert::same($result, []);
}, UnevenIteratorSourcesException::class);
