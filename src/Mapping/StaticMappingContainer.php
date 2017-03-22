<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\Type;

class StaticMappingContainer implements \Dogma\Mapping\MappingContainer
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
