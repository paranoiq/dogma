<?php

namespace Dogma\Tests\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\TypeHandler;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';

$handler = new TypeHandler();
$mapper = new Mapper(new StaticMappingContainer([]));

$typeType = Type::get(Type::class);

// acceptType()
Assert::false($handler->acceptsType(Type::get(Assert::class)));
Assert::true($handler->acceptsType($typeType));

// getParameters()
Assert::same($handler->getParameters($typeType), null);

// createInstance()
$typeInstance = $handler->createInstance($typeType, 'array<int>', $mapper);
Assert::same($typeInstance, Type::arrayOf(Type::INT));

// exportInstance()
Assert::same($handler->exportInstance($typeType, $typeInstance, $mapper), 'array<int>');
