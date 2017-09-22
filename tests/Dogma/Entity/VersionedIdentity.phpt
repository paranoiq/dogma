<?php

namespace Dogma\Tests\Entity;

use Dogma\Entity\Identity;
use Dogma\Entity\Map\Versioned\VersionedIdentityMap;
use Dogma\Tester\Assert;
use Dogma\Transaction\TransactionManager;

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestIdentity.php';

$map = new VersionedIdentityMap();
$manager = new TransactionManager([$map]);


// cannot create without version
Assert::throws(function () use ($map) {
    TestIdentity::get($map, 23);
}, \Dogma\Transaction\VersioningNotInitializedException::class);
Assert::same(count($map->getAll()), 0);

$manager->incrementVersion();

// creates and registers Identity
$identity = TestIdentity::get($map, 23);
Assert::type($identity, TestIdentity::class);
Assert::same(count($map->getAll()), 1);
Assert::same(count($map->getByClass('xxx')), 0);
Assert::same(count($map->getByClass(Identity::class)), 0);
Assert::same(count($map->getByClass(TestIdentity::class)), 1);

$manager->incrementVersion();

// id is resolved
Assert::same($identity->getId(), 23);

$manager->rollbackToVersion(1);

// id has been cleaned
Assert::same(count($map->getAll()), 1);

$manager->rollbackToVersion(0);

// identity has been thrown up
dump($map);
Assert::same(count($map->getAll()), 0);
