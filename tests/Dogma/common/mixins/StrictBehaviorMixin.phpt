<?php declare(strict_types = 1);

namespace Dogma\Tests\StrictBehaviorMixin;

use Dogma\StrictBehaviorMixin;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use StrictBehaviorMixin;
}

$x = new TestClass();

Assert::throws(function () {
    TestClass::method();
}, \Dogma\UndefinedMethodException::class);

Assert::throws(function () use ($x) {
    $x->method();
}, \Dogma\UndefinedMethodException::class);

Assert::throws(function () use ($x) {
    $x->property;
}, \Dogma\UndefinedPropertyException::class);

Assert::throws(function () use ($x) {
    $x->property = 1;
}, \Dogma\UndefinedPropertyException::class);

Assert::throws(function () use ($x) {
    isset($x->property);
}, \Dogma\UndefinedPropertyException::class);

Assert::throws(function () use ($x) {
    unset($x->property);
}, \Dogma\UndefinedPropertyException::class);
