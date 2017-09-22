<?php

namespace Dogma\Tests\Database\SqlGenerator;

use Dogma\Database\SqlGenerator\CreateTableGenerator;
use Dogma\Mapping\ConventionMappingBuilder;
use Dogma\Mapping\DynamicMappingContainer;
use Dogma\Mapping\MetaData\TypeMetaDataContainer;
use Dogma\Mapping\Type\DefaultOneWayHandler;
use Dogma\Mapping\Type\EnumHandler;
use Dogma\Mapping\Type\ScalarsHandler;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Test\Database\SqlGenerator\TestingType;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/TestingType.php';

$methodTypeParser = new MethodTypeParser();
$typeHandlers = [
    new EnumHandler(),
    new ScalarsHandler(),
    new DefaultOneWayHandler($methodTypeParser),
];
$typeContainer = new TypeMetaDataContainer($typeHandlers);
$mappingBuilder = new ConventionMappingBuilder($typeContainer);
$mappingContainer = new DynamicMappingContainer($mappingBuilder);
$generator = new CreateTableGenerator($mappingContainer);


$type = Type::get(TestingType::class);

//$sql = $generator->generate($type);

//bd($sql);

Assert::same(1, 1);
