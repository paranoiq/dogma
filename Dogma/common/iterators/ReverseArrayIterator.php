<?php

namespace Dogma;

class ReverseArrayIterator implements \Iterator
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
        end($this->array);
    }

    public function next()
    {
        prev($this->array);
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return key($this->array) !== null;
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return key($this->array);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return current($this->array);
    }

}
