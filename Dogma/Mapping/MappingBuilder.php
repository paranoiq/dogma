<?php

namespace Dogma\Mapping;

use Dogma\Type;

interface MappingBuilder
{
    
    public function buildMapping(Type $type): Mapping;

}
