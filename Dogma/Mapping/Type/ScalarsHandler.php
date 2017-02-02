<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Check;
use Dogma\Mapping\Mapper;
use Dogma\Type;

class ScalarsHandler implements \Dogma\Mapping\Type\Handler
{

    public function acceptsType(Type $type): bool
    {
        return $type->isScalar();
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
     * @param mixed $value
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed
     */
    public function createInstance(Type $type, $value, Mapper $mapper)
    {
        switch (true) {
            case $type->is(Type::BOOL):
                Check::bool($value);
                return $value;
            case $type->is(Type::INT):
                Check::int($value);
                return $value;
            case $type->is(Type::FLOAT):
                Check::float($value);
                return $value;
            case $type->is(Type::STRING):
                Check::string($value);
                return $value;
            case $type->is(Type::NUMERIC):
                Check::float($value);
                if ((float) ($int = (int) $value) === $value) {
                    return $int;
                }
                return $value;
            default:
                throw new \Dogma\InvalidTypeException(Type::SCALAR, $value);
        }
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper)
    {
        return $instance;
    }

}
