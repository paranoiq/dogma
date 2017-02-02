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

class DefaultOneWayHandler extends \Dogma\Mapping\Type\ConstructorHandler implements \Dogma\Mapping\Type\Handler
{

    public function acceptsType(Type $type): bool
    {
        return !$type->isScalar() && !$type->isArray();
    }

    /**
     * @throws \Dogma\Mapping\Type\OneWayHandlerException
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper)
    {
        throw new \Dogma\Mapping\Type\OneWayHandlerException($instance, $this);
    }

}
