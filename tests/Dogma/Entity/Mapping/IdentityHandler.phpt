<?php

namespace Dogma\Tests\Entity\Mapping;

use Dogma\Entity\Map\IdentityMap;
use Dogma\Entity\Mapping\IdentityHandler;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\Handler;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../TestIdentity.php';

$map = new IdentityMap();
$handler = new IdentityHandler($map);
$mapper = new Mapper(new StaticMappingContainer([]));

$identityType = Type::get(TestIdentity::class);

// acceptsType()
Assert::false($handler->acceptsType(Type::get(Assert::class)));
Assert::true($handler->acceptsType($identityType));

// getParameters()
Assert::same($handler->getParameters($identityType), [Handler::SINGLE_PARAMETER => Type::get(Type::INT)]);

// createInstance()
$instance = $handler->createInstance($identityType, 1, $mapper);
Assert::type($instance, TestIdentity::class);
Assert::same($instance->getId(), 1);
Assert::true($map->contains($instance));

// export instance()
$values = $handler->exportInstance($identityType, $instance, $mapper);
Assert::same($values, 1);
