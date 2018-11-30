<?php declare(strict_types = 1);

namespace Dogma\Tests\NonIterableMixin;

use Dogma\NonIterableMixin;
use Dogma\NonIterableObjectException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass implements \IteratorAggregate
{
    use NonIterableMixin;

}

Assert::throws(function (): void {
    $x = new TestClass();
    foreach ($x as $y) {
        $y = 0;
    }
}, NonIterableObjectException::class);
