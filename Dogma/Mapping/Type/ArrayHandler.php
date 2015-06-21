<?php

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Type;

/**
 * Creates an array containing specified items from raw data and vice versa
 */
class ArrayHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;
    
    public function acceptsType(Type $type): bool
    {
        return $type->is(Type::PHP_ARRAY);
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
     * @param mixed[] $items
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function createInstance(Type $type, $items, Mapper $mapper): array
    {
        $itemType = $type->getItemType();
        if ($itemType !== null && $itemType->getName() !== Type::MIXED) {
            $array = [];
            foreach ($items as $item) {
                $array[] = $mapper->map($itemType, [Handler::SINGLE_PARAMETER => $item]);
            }
            return $array;
        } else {
            return $items;
        }
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed[] $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): array
    {
        $array = [];
        if (count($instance) < 1) {
            return $array;
        }
        $itemType = $type->getItemType();
        // terminate mapping on MIXED
        if ($itemType === Type::get(Type::MIXED)) {
            return $instance;
        }
        foreach ($instance as $item) {
            $itemData = $mapper->reverseMap($itemType, $item);
            if (count($itemData) === 1 && isset($itemData[self::SINGLE_PARAMETER])) {
                $itemData = $itemData[self::SINGLE_PARAMETER];
            }
            $array[] = $itemData;
        }
        return $array;
    }

}
