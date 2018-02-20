<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use Dogma\BitSize;
use Dogma\Sign;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';

// more complex examples:

// without params
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
