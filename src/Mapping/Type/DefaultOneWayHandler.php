<?php declare(strict_types = 1);
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

class DefaultOneWayHandler extends \Dogma\Mapping\Type\ConstructorHandler implements \Dogma\Mapping\Type\TypeHandler
{

    public function acceptsType(Type $type): bool
    {
        return !$type->isScalar() && !$type->isArray();
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @throws \Dogma\Mapping\Type\OneWayHandlerException
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): void
    {
        throw new \Dogma\Mapping\Type\OneWayHandlerException($instance, $this);
    }

}
