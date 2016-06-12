<?php

namespace Dogma\Tests\Type;

use Dogma\BitSize;
use Dogma\Sign;
use Dogma\Tester\Assert;
use Dogma\Tuple;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';


Assert::same(Type::fromId(Type::INT)->getId(), Type::INT);

// get()
$int = Type::get(Type::INT);
Assert::false($int->isNullable());
Assert::true($int->isScalar());
Assert::false($int->isArray());
Assert::false($int->isCollection());
Assert::false($int->isTuple());
Assert::same($int->getName(), Type::INT);
Assert::same($int->getItemType(), null);

$intNull = Type::get(Type::INT, Type::NULLABLE);
Assert::true($intNull->isNullable());

$int2 = Type::get(Type::INT);
Assert::same($int, $int2);
Assert::notSame($int, $intNull);
Assert::same($int, $int2->getBaseType());


// bool()
Assert::same(Type::bool()->getId(), 'bool');
Assert::same(Type::bool(Type::NULLABLE)->getId(), 'bool?');

// int()
Assert::same(Type::int()->getId(), 'int');
Assert::same(Type::int(Type::NULLABLE)->getId(), 'int?');
Assert::same(Type::int(BitSize::BITS_64)->getId(), 'int(64)');
Assert::same(Type::int(BitSize::BITS_64, Type::NULLABLE)->getId(), 'int(64)?');
Assert::same(Type::int(BitSize::BITS_64, Sign::UNSIGNED)->getId(), 'int(64,unsigned)');
Assert::same(Type::int(BitSize::BITS_64, Sign::UNSIGNED, Type::NULLABLE)->getId(), 'int(64,unsigned)?');

// float()
Assert::same(Type::float()->getId(), 'float');
Assert::same(Type::float(Type::NULLABLE)->getId(), 'float?');
Assert::same(Type::float(BitSize::BITS_64)->getId(), 'float(64)');
Assert::same(Type::float(BitSize::BITS_64, Type::NULLABLE)->getId(), 'float(64)?');

// string()
Assert::same(Type::string()->getId(), 'string');
Assert::same(Type::string(Type::NULLABLE)->getId(), 'string?');
Assert::same(Type::string(64)->getId(), 'string(64)');
Assert::same(Type::string(64, Type::NULLABLE)->getId(), 'string(64)?');
Assert::same(Type::string(64, 'ascii')->getId(), 'string(64,ascii)');
Assert::same(Type::string(64, 'ascii', Type::NULLABLE)->getId(), 'string(64,ascii)?');

// callable()
Assert::same(Type::callable()->getId(), 'callable');
Assert::same(Type::callable(Type::NULLABLE)->getId(), 'callable?');

// arrayOf()
$arrayInt = Type::arrayOf(Type::INT);
Assert::false($arrayInt->isNullable());
Assert::false($arrayInt->isScalar());
Assert::true($arrayInt->isArray());
Assert::false($arrayInt->isCollection());
Assert::false($arrayInt->isTuple());
Assert::same($arrayInt->getName(), Type::PHP_ARRAY);
Assert::same($arrayInt->getItemType(), Type::get(Type::INT));

$arrayIntNull = Type::arrayOf(Type::INT, Type::NULLABLE);
Assert::true($arrayIntNull->isNullable());

$arrayInt2 = Type::arrayOf(Type::INT);
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
$collectionInt = Type::collectionOf(\SplFixedArray::class, Type::INT);
Assert::false($collectionInt->isNullable());
Assert::false($collectionInt->isScalar());
Assert::false($collectionInt->isArray());
Assert::true($collectionInt->isCollection());
Assert::false($collectionInt->isTuple());
Assert::same($collectionInt->getName(), \SplFixedArray::class);
Assert::same($collectionInt->getItemType(), Type::get(Type::INT));

$collectionIntNull = Type::collectionOf(\SplFixedArray::class, Type::INT, Type::NULLABLE);
Assert::true($collectionIntNull->isNullable());

$collectionInt2 = Type::collectionOf(\SplFixedArray::class, Type::INT);
$collectionString = Type::collectionOf(\SplFixedArray::class, Type::STRING);
$collection = Type::get(\SplFixedArray::class);
Assert::same($collectionInt, $collectionInt2);
Assert::notSame($collectionInt, $collectionString);
Assert::notSame($collectionInt, $collection);
Assert::notSame($collectionInt, $collectionIntNull);
Assert::same($collection, $collectionString->getBaseType());
Assert::same($collection, $collection->getBaseType());
Assert::same($collection, $collectionIntNull->getBaseType());


// tupleOf
$tupleIntString = Type::tupleOf(Type::INT, Type::STRING);
Assert::false($tupleIntString->isNullable());
Assert::false($tupleIntString->isScalar());
Assert::false($tupleIntString->isArray());
Assert::false($tupleIntString->isCollection());
Assert::true($tupleIntString->isTuple());
Assert::same($tupleIntString->getName(), Tuple::class);
Assert::same($tupleIntString->getItemType(), [Type::get(Type::INT), Type::get(Type::STRING)]);

$tupleIntStringNull = Type::tupleOf(Type::INT, Type::STRING, Type::NULLABLE);
Assert::true($tupleIntStringNull->isNullable());

$tupleIntString2 = Type::tupleOf(Type::INT, Type::STRING);
$tupleStringInt = Type::tupleOf(Type::STRING, Type::INT);
$tuple = Type::get(Tuple::class);
Assert::same($tupleIntString, $tupleIntString2);
Assert::notSame($tupleIntString, $tupleStringInt);
Assert::notSame($tupleIntString, $tuple);
Assert::notSame($tupleIntString, $tupleIntStringNull);
Assert::same($tuple, $tupleStringInt->getBaseType());
Assert::same($tuple, $tuple->getBaseType());
Assert::same($tuple, $tupleIntStringNull->getBaseType());


// fromId(), getId()
$id = 'Dogma\\Tuple<int,string,Dogma\\Tuple<int,array<int>,int>,SplFixedArray<int>>';
$type = Type::fromId($id);
$expected = Type::tupleOf(
    Type::INT,
    Type::STRING,
    Type::tupleOf(
        Type::INT,
        Type::arrayOf(Type::INT),
        Type::INT
    ),
    Type::collectionOf(\SplFixedArray::class, Type::INT)
);
Assert::same($type, $expected);
Assert::same($expected->getId(), $id);

// with nullable
$id = 'Dogma\\Tuple<int?,string,Dogma\\Tuple<int,array<int>,int>?,SplFixedArray<int>>?';
$type = Type::fromId($id);
$expected = Type::tupleOf(
    Type::get(Type::INT, Type::NULLABLE),
    Type::STRING,
    Type::tupleOf(
        Type::INT,
        Type::arrayOf(Type::INT),
        Type::INT,
        Type::NULLABLE
    ),
    Type::collectionOf(\SplFixedArray::class, Type::INT),
    Type::NULLABLE
);
Assert::same($type, $expected);
Assert::same($expected->getId(), $id);

// with nullable and params
$id = 'Dogma\\Tuple<int?,string(20),Dogma\\Tuple<int(16,unsigned),array<int>,int>?,SplFixedArray<int>>?';
$type = Type::fromId($id);
$expected = Type::tupleOf(
    Type::int(Type::NULLABLE),
    Type::string(20),
    Type::tupleOf(
        Type::int(BitSize::BITS_16, Sign::UNSIGNED),
        Type::arrayOf(Type::INT),
        Type::INT,
        Type::NULLABLE
    ),
    Type::collectionOf(\SplFixedArray::class, Type::INT),
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
