<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Enum;
use Dogma\EnumInt;
use Dogma\EnumString;
use Dogma\Mapping\Mapper;
use Dogma\Type;

/**
 * Creates an enum from raw value and vice versa
 */
class EnumHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(EnumInt::class) || $type->isImplementing(EnumString::class);
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
     * @return \Dogma\EnumInt|\Dogma\EnumString
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        return call_user_func([$type->getName(), 'get'], $value);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param \Dogma\EnumInt|\Dogma\EnumString $enum
     * @param \Dogma\Mapping\Mapper $mapper
     * @return int|string
     */
    public function exportInstance(Type $type, $enum, Mapper $mapper)
    {
        return $enum->getValue();
    }

}
