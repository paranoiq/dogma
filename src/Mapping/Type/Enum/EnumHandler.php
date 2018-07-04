<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type\Enum;

use Dogma\Enum\IntEnum;
use Dogma\Enum\StringEnum;
use Dogma\Mapping\Mapper;
use Dogma\Mapping\Type\TypeHandler;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use function call_user_func;

/**
 * Creates an enum from raw value and vice versa
 */
class EnumHandler implements TypeHandler
{
    use StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(IntEnum::class) || $type->isImplementing(StringEnum::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return string[]|null
     */
    public function getParameters(Type $type): ?array
    {
        return null;
    }

    /**
     * @param \Dogma\Type $type
     * @param int|string $value
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Enum\IntEnum|\Dogma\Enum\StringEnum
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        return call_user_func([$type->getName(), 'get'], $value);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param \Dogma\Enum\IntEnum|\Dogma\Enum\StringEnum $enum
     * @param \Dogma\Mapping\Mapper $mapper
     * @return int|string
     */
    public function exportInstance(Type $type, $enum, Mapper $mapper)
    {
        return $enum->getValue();
    }

}
