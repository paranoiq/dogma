<?php declare(strict_types = 1);

namespace Dogma\Tests\StrictBehaviorMixin;

use Dogma\StrictBehaviorMixin;
use Dogma\Tester\Assert;
use Dogma\UndefinedMethodException;
use Dogma\UndefinedPropertyException;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass
{
    use StrictBehaviorMixin;
}

$x = new TestClass();

Assert::throws(function () {
    TestClass::method();
}, UndefinedMethodException::class);

Assert::throws(function () use ($x) {
    $x->method();
}, UndefinedMethodException::class);

Assert::throws(function () use ($x) {
    $x->property;
}, UndefinedPropertyException::class);

Assert::throws(function () use ($x) {
    $x->property = 1;
}, UndefinedPropertyException::class);

Assert::throws(function () use ($x) {
    isset($x->property);
}, UndefinedPropertyException::class);

Assert::throws(function () use ($x) {
    unset($x->property);
}, UndefinedPropertyException::class);
