<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Type;

class TypeTypeHandler implements TypeHandler
{

    public function acceptsType(Type $type): bool
    {
        return $type->is(Type::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return \Dogma\Type[]|null
     */
    public function getParameters(Type $type): ?array
    {
        return null;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param string $typeId
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Type
     */
    public function createInstance(Type $type, $typeId, Mapper $mapper): Type
    {
        return Type::fromId($typeId);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param \Dogma\Type $typeInstance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return string
     */
    public function exportInstance(Type $type, $typeInstance, Mapper $mapper): string
    {
        return $typeInstance->getId();
    }

}
