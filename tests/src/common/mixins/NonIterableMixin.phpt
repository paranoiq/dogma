<?php declare(strict_types = 1);

namespace Dogma\Tests\NonIterableMixin;

use Dogma\NonIterableMixin;
use Dogma\NonIterableObjectException;
use Dogma\Tester\Assert;
use IteratorAggregate;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass implements IteratorAggregate
{
    use NonIterableMixin;

}

Assert::throws(static function (): void {
    $x = new TestClass();
    foreach ($x as $y) {
        $y = 0;
    }
}, NonIterableObjectException::class);
