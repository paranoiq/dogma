<?php

namespace Dogma;

class ReverseArrayIterator implements \Iterator
{
    use \Dogma\StrictBehaviorMixin;

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
