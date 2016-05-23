<?php

namespace Dogma;

class ArrayIterator implements \Iterator
{
    use StrictBehaviorMixin;

    /** @var mixed[] */
    private $array;

    /**
     * @param mixed[] $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function rewind()
    {
        reset($this->array);
    }

    public function next()
    {
        next($this->array);
    }

    public function valid(): bool
    {
        return key($this->array) !== null;
    }

    /**
     * @return int|string
     */
    public function key()
    {
        return key($this->array);
    }

    public function current()
    {
        return current($this->array);
    }

}
