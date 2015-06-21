<?php

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Tuple;
use Dogma\Type;

class TupleHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->is(Tuple::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return \Dogma\Type[]
     */
    public function getParameters(Type $type): array
    {
        return $type->getItemType();
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed[] $items
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Tuple
     */
    public function createInstance(Type $type, $items, Mapper $mapper): Tuple
    {
        return new Tuple(...$items);
    }

    /**
     * @param \Dogma\Type $type
     * @param \Dogma\Tuple $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): array
    {
        return $instance->toArray();
    }

}
