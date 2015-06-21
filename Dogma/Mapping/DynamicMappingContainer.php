<?php

namespace Dogma\Mapping;

use Dogma\Type;

class DynamicMappingContainer implements MappingContainer
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Mapping\MappingBuilder */
    private $mappingBuilder;

    /** @var \Dogma\Mapping\Mapping[] (string $typeId => $mapping) */
    private $mappings = [];

    public function __construct(MappingBuilder $mappingBuilder)
    {
        $this->mappingBuilder = $mappingBuilder;
    }
    
    public function getMapping(Type $type): Mapping
    {
        $typeId = $type->getId();
        if (!isset($this->mappings[$typeId])) {
            $this->mappings[$typeId] = $this->mappingBuilder->buildMapping($type);
        }
        return $this->mappings[$typeId];
    }

}
