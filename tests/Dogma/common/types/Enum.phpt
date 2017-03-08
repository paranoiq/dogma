<?php

namespace Dogma\Tests\Enum;

use Dogma\InvalidTypeException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class TestEnum extends \Dogma\Enum
{

    public const ONE = 1;
    public const TWO = 2;
    public const THREE = 3;

}

$one = TestEnum::get(TestEnum::ONE);
$oneAgaing = TestEnum::get(TestEnum::ONE);
$two = TestEnum::get(TestEnum::TWO);
$three = TestEnum::get(TestEnum::THREE);

// get()
Assert::type($one, TestEnum::class);
Assert::same($one, $oneAgaing);

// getValue()
Assert::same($one->getValue(), 1);

// getConstantName()
Assert::same($one->getConstantName(), 'ONE');

// equals()
Assert::exception(function () use ($one) {
    $one->equals(new \stdClass);
}, InvalidTypeException::class);
Assert::false($one->equals($two));
Assert::true($one->equals($oneAgaing));

// isValid()
Assert::false(TestEnum::isValid(4));
Assert::true(TestEnum::isValid(1));

// getAllowedValues()
Assert::same(TestEnum::getAllowedValues(), [
    'ONE' => 1,
    'TWO' => 2,
    'THREE' => 3,
]);

// getInstances()
Assert::same(TestEnum::getInstances(), [
    1 => $one,
    2 => $two,
    3 => $three,
]);
