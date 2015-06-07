<?php

namespace Dogma\Tests\Type;

use Dogma\Tester\Assert;
use Dogma\Tuple;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';


// get()
$int = Type::get(Type::INTEGER);
Assert::false($int->isNullable());
Assert::true($int->isScalar());
Assert::false($int->isArray());
Assert::false($int->isCollection());
Assert::false($int->isTuple());
Assert::same($int->getName(), Type::INTEGER);
Assert::same($int->getItemType(), null);

$intNull = Type::get(Type::INTEGER, Type::NULLABLE);
Assert::true($intNull->isNullable());

$int2 = Type::get(Type::INTEGER);
Assert::same($int, $int2);
Assert::notSame($int, $intNull);
Assert::same($int, $int2->getBaseType());


// arrayOf()
$arrayInt = Type::arrayOf(Type::INTEGER);
Assert::false($arrayInt->isNullable());
Assert::false($arrayInt->isScalar());
Assert::true($arrayInt->isArray());
Assert::false($arrayInt->isCollection());
Assert::false($arrayInt->isTuple());
Assert::same($arrayInt->getName(), Type::PHP_ARRAY);
Assert::same($arrayInt->getItemType(), Type::get(Type::INTEGER));

$arrayIntNull = Type::arrayOf(Type::INTEGER, Type::NULLABLE);
Assert::true($arrayIntNull->isNullable());

$arrayInt2 = Type::arrayOf(Type::INTEGER);
$arrayString = Type::arrayOf(Type::STRING);
$array = Type::get(Type::PHP_ARRAY);
Assert::same($arrayInt, $arrayInt2);
Assert::notSame($arrayInt, $arrayString);
Assert::notSame($arrayInt, $array);
Assert::notSame($arrayInt, $arrayIntNull);
Assert::same($array, $arrayString->getBaseType());
Assert::same($array, $array->getBaseType());
Assert::same($array, $arrayIntNull->getBaseType());


// collectionOf()
$collectionInt = Type::collectionOf(\SplFixedArray::class, Type::INTEGER);
Assert::false($collectionInt->isNullable());
Assert::false($collectionInt->isScalar());
Assert::false($collectionInt->isArray());
Assert::true($collectionInt->isCollection());
Assert::false($collectionInt->isTuple());
Assert::same($collectionInt->getName(), \SplFixedArray::class);
Assert::same($collectionInt->getItemType(), Type::get(Type::INTEGER));

$collectionIntNull = Type::collectionOf(\SplFixedArray::class, Type::INTEGER, Type::NULLABLE);
Assert::true($collectionIntNull->isNullable());

$collectionInt2 = Type::collectionOf(\SplFixedArray::class, Type::INTEGER);
$collectionString = Type::collectionOf(\SplFixedArray::class, Type::STRING);
$collection = Type::get(\SplFixedArray::class);
Assert::same($collectionInt, $collectionInt2);
Assert::notSame($collectionInt, $collectionString);
Assert::notSame($collectionInt, $collection);
Assert::notSame($collectionInt, $collectionIntNull);
Assert::same($collection, $collectionString->getBaseType());
Assert::same($collection, $collection->getBaseType());
Assert::same($collection, $collectionIntNull->getBaseType());


// tuppleOf
$tupleIntString = Type::tupleOf(Type::INTEGER, Type::STRING);
Assert::false($tupleIntString->isNullable());
Assert::false($tupleIntString->isScalar());
Assert::false($tupleIntString->isArray());
Assert::false($tupleIntString->isCollection());
Assert::true($tupleIntString->isTuple());
Assert::same($tupleIntString->getName(), Tuple::class);
Assert::same($tupleIntString->getItemType(), [Type::get(Type::INTEGER), Type::get(Type::STRING)]);

$tupleIntStringNull = Type::tupleOf(Type::INTEGER, Type::STRING, Type::NULLABLE);
Assert::true($tupleIntStringNull->isNullable());

$tupleIntString2 = Type::tupleOf(Type::INTEGER, Type::STRING);
$tupleStringInt = Type::tupleOf(Type::STRING, Type::INTEGER);
$tuple = Type::get(Tuple::class);
Assert::same($tupleIntString, $tupleIntString2);
Assert::notSame($tupleIntString, $tupleStringInt);
Assert::notSame($tupleIntString, $tuple);
Assert::notSame($tupleIntString, $tupleIntStringNull);
Assert::same($tuple, $tupleStringInt->getBaseType());
Assert::same($tuple, $tuple->getBaseType());
Assert::same($tuple, $tupleIntStringNull->getBaseType());


// fromId(), getId()
$id = 'Dogma\\Tuple<integer,string,Dogma\\Tuple<integer,array<integer>,integer>,SplFixedArray<integer>>';
$type = Type::fromId($id);
$expected = Type::tupleOf(
    Type::INTEGER,
    Type::STRING,
    Type::tupleOf(
        Type::INTEGER,
        Type::arrayOf(Type::INTEGER),
        Type::INTEGER
    ),
    Type::collectionOf(\SplFixedArray::class, Type::INTEGER)
);
Assert::same($type, $expected);
Assert::same($expected->getId(), $id);

$id = 'Dogma\\Tuple<integer?,string,Dogma\\Tuple<integer,array<integer>,integer>?,SplFixedArray<integer>>?';
$type = Type::fromId($id);
$expected = Type::tupleOf(
    Type::get(Type::INTEGER, Type::NULLABLE),
    Type::STRING,
    Type::tupleOf(
        Type::INTEGER,
        Type::arrayOf(Type::INTEGER),
        Type::INTEGER,
        Type::NULLABLE
    ),
    Type::collectionOf(\SplFixedArray::class, Type::INTEGER),
    Type::NULLABLE
);
Assert::same($type, $expected);
Assert::same($expected->getId(), $id);


// getInstance()
$date = Type::get(\DateTime::class);
Assert::equal($date->getInstance('2001-02-03 04:05:06'), new \DateTime('2001-02-03 04:05:06'));

// is()
Assert::true($date->is(\DateTime::class));
Assert::false($date->is(\DateTimeInterface::class));
Assert::false($date->is(\DateTimeImmutable::class));

// isImplementing()
Assert::true($date->isImplementing(\DateTime::class));
Assert::true($date->isImplementing(\DateTimeInterface::class));
Assert::false($date->isImplementing(\DateTimeImmutable::class));
