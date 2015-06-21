<?php

namespace Dogma\Mapping;

use Dogma\Type;

class StaticMappingContainer implements MappingContainer
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Mapping\Mapping[] (string $typeId => $mapping) */
    private $mappings;

    public function __construct(array $mappings)
    {
        $this->mappings = $mappings;
    }
    
    public function getMapping(Type $type): Mapping
    {
        $typeId = $type->getId();
        if (!isset($this->mappings[$typeId])) {
            throw new \Dogma\Mapping\MappingNotFoundException($type);
        }
        return $this->mappings[$typeId];
    }

}
