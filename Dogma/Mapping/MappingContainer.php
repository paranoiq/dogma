<?php

namespace Dogma\Mapping;

use Dogma\Type;

interface MappingContainer
{
    
    public function getMapping(Type $type): Mapping;

}
