<?php declare(strict_types = 1);

namespace Dogma\Tests\Enum;

use Dogma\Enum\IntEnum;
use Dogma\InvalidTypeException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class TestEnum extends IntEnum
{

    public const ONE = 1;
    public const TWO = 2;
    public const THREE = 3;

}

$one = TestEnum::get(TestEnum::ONE);
$oneAgain = TestEnum::get(TestEnum::ONE);
$two = TestEnum::get(TestEnum::TWO);
$three = TestEnum::get(TestEnum::THREE);

// get()
Assert::type($one, TestEnum::class);
Assert::same($one, $oneAgain);

// getValue()
Assert::same($one->getValue(), 1);

// getConstantName()
Assert::same($one->getConstantName(), 'ONE');

// equals()
Assert::exception(function () use ($one): void {
    $one->equals(new \stdClass());
}, InvalidTypeException::class);
Assert::false($one->equals($two));
Assert::true($one->equals($oneAgain));

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
