<?php declare(strict_types = 1);

namespace Dogma\Tests\NonSerializableMixin;

use Dogma\NonSerializableMixin;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use NonSerializableMixin;
}

Assert::throws(function () {
    $x = new TestClass();
    $y = serialize($x);
}, \Dogma\NonSerializableObjectException::class);

Assert::throws(function () {
    $y = 'O:42:"Dogma\Tests\NonSerializableMixin\TestClass":0:{}';
    $x = unserialize($y);
}, \Dogma\NonSerializableObjectException::class);
