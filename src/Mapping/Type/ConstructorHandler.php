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
use Dogma\Reflection\MethodTypeParser;
use Dogma\Type;
use ReflectionClass;

/**
 * Creates instance via keyword 'new' by filling constructor parameters
 */
abstract class ConstructorHandler implements TypeHandler
{

    /** @var \Dogma\Reflection\MethodTypeParser */
    private $parser;

    public function __construct(MethodTypeParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * @param \Dogma\Type $type
     * @return \Dogma\Type[]
     */
    public function getParameters(Type $type): array
    {
        $class = new ReflectionClass($type->getName());
        $constructor = $class->getConstructor();

        return $this->parser->getParameterTypes($constructor);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param mixed[] $parameters
     * @param \Dogma\Mapping\Mapper $mapper
     * @return object
     */
    public function createInstance(Type $type, $parameters, Mapper $mapper): object
    {
        $orderedParams = [];
        foreach ($this->getParameters($type) as $name => $paramType) {
            // it is up to class constructor to check the types!
            $orderedParams[] = $parameters[$name];
        }
        return $type->getInstance(...$orderedParams);
    }

}
