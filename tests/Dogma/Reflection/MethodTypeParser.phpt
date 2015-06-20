<?php

namespace Dogma\Tests\Reflection;

use Dogma\Reflection\InvalidMethodAnnotationException;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Reflection\UnprocessableParameterException;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/MethodTypeParserTestClass.php';


$parser = new MethodTypeParser();
$class = new \ReflectionClass(MethodTypeParserTestClass::class);
$rawKeys = ['types', 'nullable', 'reference', 'variadic', 'optional'];

Assert::same(
    $parser->getTypes($class->getMethod('testReturn')),
    ['@return' => Type::get(Type::INT)]
);

$test = function ($methodName, $expectedRaw, $expectedType) use ($parser, $class, $rawKeys) {
    if (is_string($expectedRaw)) {
        Assert::throws(function () use ($parser, $class, $methodName) {
            $parser->getTypesRaw($class->getMethod($methodName));
        }, $expectedRaw);
    } else {
        foreach ($expectedRaw as $name => &$val) {
            $val = array_combine($rawKeys, array_pad($val, 5, false));
        }
        $params = $parser->getTypesRaw($class->getMethod($methodName));
        Assert::same($params, $expectedRaw);
    }
    if (is_string($expectedType)) {
        Assert::throws(function () use ($parser, $class, $methodName) {
            $parser->getTypes($class->getMethod($methodName));
        }, $expectedType);
    } else {
        $params = $parser->getParameterTypes($class->getMethod($methodName));
        Assert::same($params, $expectedType);
    }
};

$test(
    'testNoType',
    ['one' => [[]]],
    ['one' => Type::get(Type::MIXED)]
);
$test(
    'testNullable',
    ['one' => [[], true, false, false, true]],
    ['one' => Type::get(Type::MIXED, Type::NULLABLE)]
);
$test(
    'testTwoParams',
    ['one' => [[]], 'two' => [[]]],
    ['one' => Type::get(Type::MIXED), 'two' => Type::get(Type::MIXED)]
);
$test(
    'testArray',
    ['one' => [[Type::PHP_ARRAY]]],
    ['one' => Type::get(Type::PHP_ARRAY)]
);
$test(
    'testCallable',
    ['one' => [[Type::PHP_CALLABLE]]],
    ['one' => Type::get(Type::PHP_CALLABLE)]
);
$test(
    'testClass',
    ['one' => [[\Exception::class]]],
    ['one' => Type::get(\Exception::class)]
);
$test(
    'testSelf',
    ['one' => [[MethodTypeParserTestClass::class]]],
    ['one' => Type::get(MethodTypeParserTestClass::class)]
);
$test(
    'testReference',
    ['one' => [[], false, true]],
    UnprocessableParameterException::class
);
$test(
    'testVariadic',
    ['one' => [[], false, false, true, true]],
    UnprocessableParameterException::class
);
$test(
    'testAnnotationCountMissmatch',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationCountMissmatch2',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationNameMissmatch',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotation',
    ['one' => [[Type::INT]]],
    ['one' => Type::get(Type::INT)]
);
$test(
    'testAnnotationNullable',
    ['one' => [[Type::INT], true, false, false, true]],
    ['one' => Type::get(Type::INT, Type::NULLABLE)]
);
$test(
    'testAnnotationWithNull',
    ['one' => [[Type::INT], true]],
    ['one' => Type::get(Type::INT, Type::NULLABLE)]
);
$test(
    'testAnnotationWithNullNullable',
    ['one' => [[Type::INT], true, false, false, true]],
    ['one' => Type::get(Type::INT, Type::NULLABLE)]
);
$test(
    'testAnnotationIncompleteClass',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationNonExistingClass',
    InvalidMethodAnnotationException::class,
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationClass',
    ['one' => [[\Exception::class]]],
    ['one' => Type::get(\Exception::class)]
);
$test(
    'testAnnotationSelf',
    ['one' => [[MethodTypeParserTestClass::class]]],
    ['one' => Type::get(MethodTypeParserTestClass::class)]
);
$test(
    'testAnnotationStatic',
    ['one' => [[MethodTypeParserTestClass::class]]],
    ['one' => Type::get(MethodTypeParserTestClass::class)]
);
$test(
    'testAnnotationClassClass',
    ['one' => [[\Exception::class]]],
    ['one' => Type::get(\Exception::class)]
);
$test(
    'testAnnotationWithoutName',
    ['one' => [[Type::INT]], 'two' => [[Type::STRING]]],
    ['one' => Type::get(Type::INT), 'two' => Type::get(Type::STRING)]
);
$test(
    'testAnnotationMoreTypes',
    ['one' => [[Type::INT, Type::STRING]]],
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationDimmensionMissmatch',
    ['one' => [[\Exception::class, 'int[]']]],
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationArrayBrackets',
    ['one' => [['int[]']]],
    ['one' => Type::arrayOf(Type::INT)]
);
$test(
    'testAnnotationArrayOfType',
    ['one' => [[Type::PHP_ARRAY, 'int[]']]],
    ['one' => Type::arrayOf(Type::INT)]
);
$test(
    'testAnnotationArrayOfTypes',
    ['one' => [[Type::PHP_ARRAY, 'int[]', 'string[]']]],
    InvalidMethodAnnotationException::class
);
$test(
    'testAnnotationCollectionOfType',
    ['one' => [[\SplFixedArray::class, 'int[]']]],
    ['one' => Type::collectionOf(\SplFixedArray::class, Type::INT)]
);
$test(
    'testAnnotationCollectionOfTypes',
    ['one' => [[\SplFixedArray::class, 'int[]', 'string[]']]],
    InvalidMethodAnnotationException::class
);
