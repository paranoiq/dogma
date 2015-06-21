<?php

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Type;

trait ExportableHandlerMixin
{

    /**
     * @param \Dogma\Type $type
     * @param object $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper)
    {
        return $instance->export();
    }

}
