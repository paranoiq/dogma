<?php

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\Type;

/**
 * Interface for creating instances from raw data and vice versa
 */
interface Handler
{

    /**
     * Used as a key in array returned by getParameters().
     * Indicates only one parameter is expected instead of array.
     */
    const SINGLE_PARAMETER = '';

    /**
     * Returns true if hsndler accepts the type represented by $type parameter.
     */
    public function acceptsType(Type $type): bool;

    /**
     * Returns list of parameters where keys are the expexted keys in parameters array received by createInstance()
     * and keys returned by exportInstance(). Values of array are the expected/returned types of parameters.
     *
     * When a single parameter is expected (not an array), constant SINGLE_PARAMETER, should be used as the key.
     *
     * When both key and type do not matter, value NULL may be returned. NULL is translated by MappingBuilder to:
     * [Handler::SINGLE_PARAMETER => Type(Type::MIXED)]
     *
     * Type::MIXED is the only type, that stops MappingBuilder from further unwraping the type definition. Use this
     * type when you don't want the value to be changed at all.
     *
     * @param \Dogma\Type $type
     * @return \Dogma\Type[]|null ($parameterName => $type)
     */
    public function getParameters(Type $type);

    /**
     * Expects array of parameters or a single parameter and returs the instantiated value of given type.
     *
     * When all parameters are be NULL, only a single NULL value is expected instead of array of NULLs.
     *
     * May use given Mapper for mapping some intermediate values.
     *
     * @param \Dogma\Type $type
     * @param mixed|mixed[]|null $parameters
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed
     */
    public function createInstance(Type $type, $parameters, Mapper $mapper);

    /**
     * Expects an instance and returs array of its original parameters or a single parameter.
     *
     * May use given Mapper for reverse mapping some intermediate values.
     *
     * @param \Dogma\Type $type
     * @param mixed|null $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed|mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper);

}
