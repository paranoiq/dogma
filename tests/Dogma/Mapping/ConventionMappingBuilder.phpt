<?php

namespace Dogma\Tests\Mapping;

use Dogma\Mapping\ConventionMappingBuilder;
use Dogma\Mapping\MappingStep;
use Dogma\Mapping\MetaData\TypeMetaDataContainer;
use Dogma\Mapping\Naming\ShortFieldNamingStrategy;
use Dogma\Mapping\Type\ExportableHandler;
use Dogma\Mapping\Type\Handler;
use Dogma\Mapping\Type\ScalarsHandler;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/ExportableTestClass.php';
require_once __DIR__ . '/OuterTestClass.php';

$scalarsHandler = new ScalarsHandler();
$exportableHandler = new ExportableHandler(new MethodTypeParser());
$typeContainer = new TypeMetaDataContainer([$scalarsHandler, $exportableHandler]);
$namingStrategy = new ShortFieldNamingStrategy();
$builder = new ConventionMappingBuilder($typeContainer, $namingStrategy, '.');

$exportableType = Type::get(ExportableTestClass::class);
$outerType = Type::get(OuterTestClass::class);

$mapping = $builder->buildMapping($exportableType);

$steps = $mapping->getSteps();
Assert::equal($steps[0], new MappingStep(
    Type::get(Type::INT),
    $scalarsHandler,
    ['one' => Handler::SINGLE_PARAMETER],
    'one'
));
Assert::equal($steps[1], new MappingStep(
    Type::get(Type::FLOAT),
    $scalarsHandler,
    ['two' => Handler::SINGLE_PARAMETER],
    'two'
));
Assert::equal($steps[2], new MappingStep(
    Type::get(ExportableTestClass::class),
    $exportableHandler,
    ['one' => 'one', 'two' => 'two'],
    Handler::SINGLE_PARAMETER
));

$mapping = $builder->buildMapping($outerType);

$steps = $mapping->getSteps();
Assert::equal($steps[0], new MappingStep(
    Type::get(Type::INT),
    $scalarsHandler,
    ['one' => Handler::SINGLE_PARAMETER],
    'three.one'
));
Assert::equal($steps[1], new MappingStep(
    Type::get(Type::FLOAT),
    $scalarsHandler,
    ['two' => Handler::SINGLE_PARAMETER],
    'three.two'
));
Assert::equal($steps[2], new MappingStep(
    Type::get(ExportableTestClass::class),
    $exportableHandler,
    ['three.one' => 'one', 'three.two' => 'two'],
    'three'
));
Assert::equal($steps[3], new MappingStep(
    Type::get(Type::STRING),
    $scalarsHandler,
    ['four' => Handler::SINGLE_PARAMETER],
    'four'
));
Assert::equal($steps[4], new MappingStep(
    Type::get(OuterTestClass::class),
    $exportableHandler,
    ['three' => 'three', 'four' => 'four'],
    Handler::SINGLE_PARAMETER
));
