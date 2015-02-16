<?php

namespace Dogma;

trait ImmutableArrayAccessMixin
{

    /**
     * @param mixed $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->toArray());
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @throws \BadMethodCallException
     */
    public function offsetSet($key, $value)
    {
        throw new \BadMethodCallException('Cannot modify an item of immutable list.');
    }

    /**
     * @param mixed $key
     * @throws \BadMethodCallException
     */
    public function offsetUnset($key)
    {
        throw new \BadMethodCallException('Cannot unset an item of immutable list.');
    }

}
