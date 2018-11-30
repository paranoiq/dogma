<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use Dogma\Check;
use Dogma\InvalidTypeException;
use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Type;
use Dogma\ValueOutOfRangeException;
use Tracy\Debugger;
use stdClass;

require_once __DIR__ . '/../../bootstrap.php';

class TestClass1
{

    /** @var int */
    public $a = 1;

    /** @var int */
    public $b = 2;

    /** @var int */
    public $c = 3;

}

class TestClass2
{

    /** @var int */
    public $a = 1;

    /** @var int */
    protected $b = 2;

    /** @var bool */
    private $c = true;

    public function test(): bool
    {
        return $this->c;
    }

    public static function testStatic(): bool
    {
        return true;
    }

}

$stdClassEmpty = function (): stdClass {
    return new stdClass();
};
$stdClassInt = function (): stdClass {
    $obj = new stdClass();
    $obj->a = 1;
    $obj->b = 2;
    $obj->c = 3;
    return $obj;
};
$classMapIntPublic = function (): TestClass1 {
    return new TestClass1();
};
$classMapIntMixed = function (): TestClass2 {
    return new TestClass2();
};
$resource = function () {
    return tmpfile();
};
$callable = function (): callable {
    return function () {
        return true;
    };
};
$callableFunction = 'strlen';
$callableMethod = [new TestClass2(), 'test'];
$callableStaticMethod = [TestClass2::class, 'testStatic'];

/**
 * @var mixed[][] ($key => [$value, $possibleTypes...])
 */
$subjects = [
    'null' => [null, Type::NULL],
    'false' => [false, Type::BOOL],
    'true' => [true, Type::BOOL],

    'int' => [123, Type::INT, Type::FLOAT, Type::STRING],
    'intZero' => [0, Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    'intOne' => [1, Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],

    'float' => [123.456, Type::FLOAT, Type::STRING],
    'floatNan' => [NAN],
    'floatInf' => [INF],
    // phpcs:ignore
    'floatInfNegative' => [-INF],
    'floatInt' => [123.0, Type::INT, Type::FLOAT, Type::STRING],
    'floatZero' => [0.0, Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    // phpcs:ignore
    'floatZeroNegative' => [-0.0, Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    'floatOne' => [1.0, Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],

    'stringEmpty' => ['', Type::BOOL, Type::STRING],
    'string' => ['abc', Type::STRING],
    'stringInt' => ['123', Type::INT, Type::STRING, Type::FLOAT],
    'stringIntish' => ['123abc', Type::STRING],
    'stringIntZero' => ['0', Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    'stringIntOne' => ['1', Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    'stringIntZeroish' => ['0abc', Type::STRING],
    'stringFloat' => ['123.456', Type::FLOAT, Type::STRING],
    'stringFloatish' => ['123.456abc', Type::STRING],
    'stringFloatInt' => ['123.0', Type::INT, Type::FLOAT, Type::STRING],
    'stringFloatIntish' => ['123.0abc', Type::STRING],
    'stringFloatZero' => ['0.0', Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    'stringFloatZeroish' => ['0.0abc', Type::STRING],
    'stringFloatOne' => ['1.0', Type::BOOL, Type::INT, Type::FLOAT, Type::STRING],
    'stringFloatOneish' => ['1.0abc', Type::STRING],

    'arrayEmpty' => [[], Type::PHP_ARRAY],
    'arrayVector' => [[1, 2, 3], Type::PHP_ARRAY],
    'arrayMap' => [['a' => 1, 'b' => 2, 'c' => 3], Type::PHP_ARRAY],

    'stdClassEmpty' => [$stdClassEmpty, Type::OBJECT, 'stdClass'],
    'stdClassInt' => [$stdClassInt, Type::OBJECT, 'stdClass'],
    'classMapIntPublic' => [$classMapIntPublic, Type::OBJECT, 'Dogma\Tests\Type\TestClassMapIntPublic'],
    'classMapIntMixed' => [$classMapIntMixed, Type::OBJECT, 'Dogma\Tests\Type\TestClassMapIntMixed'],

    'resource' => [$resource, Type::RESOURCE],

    'callable' => [$callable, Type::PHP_CALLABLE, Type::OBJECT],
    'callableFunction' => [$callableFunction, Type::PHP_CALLABLE, Type::STRING],
    'callableMethod' => [$callableMethod, Type::PHP_CALLABLE, Type::PHP_ARRAY],
    'callableStaticMethod' => [$callableStaticMethod, Type::PHP_CALLABLE, Type::PHP_ARRAY],
];

$types = Type::listNativeTypes();
foreach ($subjects as $name => $possibleTypes) {
    $subject = array_shift($possibleTypes);
    if ($subject instanceof \Closure) {
        $subject = $subject();
    }
    foreach ($types as $type) {
        if (is_object($subject)) {
            $copy = clone($subject);
        } else {
            $copy = $subject;
        }
        try {
            Check::type($copy, $type);
            if (!in_array($type, $possibleTypes)) {
                $before = trim(Debugger::dump($subject, true));
                $after = trim(Debugger::dump($copy, true));
                Assert::fail(sprintf('Subject %s `%s` should not be castable to type %s. Instead casted to value `%s`.', $name, $before, $type ?: 'null', $after));
            }
        } catch (\Throwable $e) {
            $class = get_class($e);
            $before = trim(Debugger::dump($subject, true));
            if ($class === \Tester\AssertException::class) {
                throw $e;
            } elseif (in_array($type, $possibleTypes)) {
                Assert::fail(sprintf('Subject %s `%s` should be casted to type %s. %s thrown instead.', $name, $before, $type, $class));
            } elseif ($class === InvalidTypeException::class
                && !($type === Type::FLOAT && is_float($subject) && (is_nan($subject) || $subject === INF || $subject === -INF))) {
                continue;
            } elseif ($class === InvalidValueException::class
                && is_float($subject) && is_nan($subject)) {
                continue;
            } elseif ($class === ValueOutOfRangeException::class
                && is_float($subject) && ($subject === INF || $subject === -INF)) {
                continue;
            } elseif ($type === Type::FLOAT && is_float($subject) && (is_nan($subject) || $subject === INF || $subject === -INF)) {
                Assert::fail(sprintf('Subject %s `%s` casted to %s should throw an InvalidValueException. %s thrown instead.', $name, $before, $type, $class));
            } else {
                Assert::fail(sprintf('Subject %s `%s` casted to %s should throw an InvalidTypeException. %s thrown instead.', $name, $before, $type, $class));
            }
        }
    }
}

Assert::true(true);
