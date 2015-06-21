<?php

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Type;
use ReflectionClass;

/**
 * Creates instance via keyword 'new' by filling constructor parameters
 */
abstract class ConstructorHandler implements \Dogma\Mapping\Type\Handler
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
     * @param \Dogma\Type $type
     * @param mixed[] $parameters
     * @param \Dogma\Mapping\Mapper $mapper
     * @return object
     */
    public function createInstance(Type $type, $parameters, Mapper $mapper)
    {
        $orderedParams = [];
        foreach ($this->getParameters($type) as $name => $paramType) {
            // it is up to class constructor to check the types!
            $orderedParams[] = $parameters[$name];
        }
        return $type->getInstance(...$orderedParams);
    }

}
