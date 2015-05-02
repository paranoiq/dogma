<?php

namespace Dogma\Tests\StaticClassMixin;

use Dogma\StaticClassMixin;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use StaticClassMixin;
}

Assert::throws(function () {
    $x = new TestClass();
}, \Dogma\StaticClassException::class);

Assert::throws(function () {
    TestClass::method();
}, \Dogma\UndefinedMethodException::class);
