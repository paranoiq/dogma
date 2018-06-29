<?php declare(strict_types = 1);

namespace Dogma\Tests\Type;

use Dogma\Collection;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../../bootstrap.php';

$int = Type::int();

$array = Type::get(Type::PHP_ARRAY);
$arrayNullable = Type::get(Type::PHP_ARRAY, Type::NULLABLE);
$arrayOfInt = Type::arrayOf($int);
$arrayOfIntNullable = Type::arrayOf($int, Type::NULLABLE);
$collection = Type::collectionOf(Collection::class, \DateTime::class);
$collectionNullable = Type::collectionOf(Collection::class, \DateTime::class, Type::NULLABLE);


// getId()
Assert::same($array->getId(), 'array<mixed>');
Assert::same($arrayNullable->getId(), 'array<mixed>?');
Assert::same($arrayOfInt->getId(), 'array<int>');
Assert::same($arrayOfIntNullable->getId(), 'array<int>?');
Assert::same($collection->getId(), 'Dogma\\Collection<DateTime>');
Assert::same($collectionNullable->getId(), 'Dogma\\Collection<DateTime>?');

// fromId()
Assert::same(Type::fromId('array'), $array);
Assert::same(Type::fromId('array?'), $arrayNullable);
Assert::same(Type::fromId('array<int>'), $arrayOfInt);
Assert::same(Type::fromId('array<int>?'), $arrayOfIntNullable);
Assert::same(Type::fromId('Dogma\\Collection<DateTime>'), $collection);
Assert::same(Type::fromId('Dogma\\Collection<DateTime>?'), $collectionNullable);

// getName()
Assert::same($array->getName(), Type::PHP_ARRAY);
Assert::same($collection->getName(), Collection::class);

// isNullable()
Assert::false($array->isNullable());
Assert::true($arrayNullable->isNullable());
Assert::false($collection->isNullable());
Assert::true($collectionNullable->isNullable());

// isSigned()
Assert::false($array->isSigned());
Assert::false($collection->isSigned());

// isUnsigned()
Assert::false($array->isUnsigned());
Assert::false($collection->isUnsigned());

// isFixed()
Assert::false($array->isFixed());
Assert::false($collection->isFixed());

// getResourceType()
Assert::null($array->getResourceType());
Assert::null($collection->getResourceType());

// getItemType()
Assert::same($array->getItemType(), Type::get(Type::MIXED));
Assert::same($arrayOfInt->getItemType(), Type::int());
Assert::same($collection->getItemType(), Type::get(\DateTime::class));

// getSize()
Assert::null($array->getSize());
Assert::null($collection->getSize());

// getEncoding()
Assert::null($array->getEncoding());
Assert::null($collection->getEncoding());

// getLocale()
Assert::null($array->getLocale());
Assert::null($collection->getLocale());

// isBool()
Assert::false($array->isBool());
Assert::false($collection->isBool());

// isInt()
Assert::false($array->isInt());
Assert::false($collection->isInt());

// isFloat()
Assert::false($array->isFloat());
Assert::false($collection->isFloat());

// isNumeric()
Assert::false($array->isNumeric());
Assert::false($collection->isNumeric());

// isString()
Assert::false($array->isString());
Assert::false($collection->isString());

// isScalar()
Assert::false($array->isScalar());
Assert::false($collection->isScalar());

// isArray()
Assert::true($array->isArray());
Assert::true($arrayOfInt->isArray());
Assert::false($collection->isArray());

// isCollection()
Assert::false($array->isCollection());
Assert::false($arrayOfInt->isCollection());
Assert::true($collection->isCollection());

// isTuple()
Assert::false($array->isTuple());
Assert::false($collection->isTuple());

// isClass()
Assert::false($array->isClass());
Assert::true($collection->isClass());

// isCallable()
Assert::false($array->isCallable());
Assert::false($collection->isCallable());

// isResource()
Assert::false($array->isResource());
Assert::false($collection->isResource());

// is()
Assert::true($array->is(Type::PHP_ARRAY));
Assert::false($array->is(\DateTime::class));
Assert::true($collection->is(Collection::class));
Assert::false($collection->is(\DateTime::class));

// isImplementing()
Assert::false($array->isImplementing(\DateTime::class));
Assert::false($array->isImplementing(Collection::class));
Assert::true($collection->isImplementing(Collection::class));

// getBaseType()
Assert::same($arrayNullable->getBaseType(), $array);
Assert::same($array->getBaseType(), $array);
Assert::same($collection->getBaseType(), Type::get(Collection::class));

// getNonNullableType()
Assert::same($arrayNullable->getNonNullableType(), $array);
Assert::same($array->getNonNullableType(), $array);
Assert::same($collectionNullable->getNonNullableType(), $collection);
Assert::same($collection->getNonNullableType(), $collection);

// getTypeWithoutParams()
Assert::same($arrayNullable->getTypeWithoutParams(), $arrayNullable);
Assert::same($array->getTypeWithoutParams(), $array);
Assert::same($collectionNullable->getTypeWithoutParams(), $collectionNullable);
Assert::same($collection->getTypeWithoutParams(), $collection);

// getInstance()
Assert::exception(function () use ($array): void {
    $array->getInstance('abc');
}, \Error::class);
Assert::equal($collection->getInstance(\DateTime::class), new Collection(\DateTime::class));
