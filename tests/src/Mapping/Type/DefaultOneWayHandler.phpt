<?php declare(strict_types = 1);

namespace Dogma\Tests\Mapping\Type;

use DateTime;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\StaticMappingContainer;
use Dogma\Mapping\Type\DefaultOneWayHandler;
use Dogma\Mapping\Type\OneWayHandlerException;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';

$paramsParser = new MethodTypeParser();
$handler = new DefaultOneWayHandler($paramsParser);
$mapper = new Mapper(new StaticMappingContainer([]));

$dateTimeType = Type::get(DateTime::class);

// acceptType()
Assert::true($handler->acceptsType($dateTimeType));
Assert::true($handler->acceptsType(Type::get('Any')));

// getParameters()
Assert::equal($handler->getParameters($dateTimeType), [
    'time' => Type::get(Type::MIXED),
    'timezone' => Type::get(Type::MIXED),
]);

// createInstance()
$dateInstance = $handler->createInstance($dateTimeType, [
    'time' => '2001-02-03 04:05:06',
    'timezone' => new \DateTimeZone('+01:00'),
], $mapper);
Assert::type($dateInstance, DateTime::class);
Assert::same($dateInstance->format('Y-m-d H:i:s'), '2001-02-03 04:05:06');

// exportInstance()
Assert::throws(function () use ($handler, $mapper, $dateTimeType): void {
    $handler->exportInstance($dateTimeType, new DateTime(), $mapper);
}, OneWayHandlerException::class);
