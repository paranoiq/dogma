<?php

namespace Dogma\Tests\NonIterableMixin;

use Dogma\NonIterableMixin;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass implements \IteratorAggregate
{
    use NonIterableMixin;
}

Assert::throws(function () {
    $x = new TestClass();
    foreach ($x as $y) {
        // pass
    }
}, \Dogma\NonIterableObjectException::class);
