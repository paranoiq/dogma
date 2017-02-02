<?php
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
