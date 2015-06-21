<?php

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Type;

class TypeHandler implements \Dogma\Mapping\Type\Handler
{

    public function acceptsType(Type $type): bool
    {
        return $type->is(Type::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return null
     */
    public function getParameters(Type $type)
    {
        return null;
    }

    /**
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
