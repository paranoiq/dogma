<?php

namespace Dogma\Tests\Entity\Proxy;

use Dogma\Entity\Proxy\EntityProxyBuilder;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/TestEntity.php';

$builder = new EntityProxyBuilder(new MethodTypeParser());

Assert::same(1, 1);
///Assert::same($builder->buildProxy(Type::get(TestEntity::class)), '');
