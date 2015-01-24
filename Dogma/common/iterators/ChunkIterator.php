<?php

namespace Dogma;

class ChunkIterator extends \IteratorIterator
{
    use StrictBehaviorMixin;

    /** @var integer */
    private $chunkSize;

    /** @var integer */
    private $key;

    /** @var mixed[] */
    private $chunk;

    /**
     * @param mixed[]|\Traversable $traversable
     * @param integer $chunkSize
     */
    public function __construct($traversable, $chunkSize)
    {
        Check::natural($chunkSize);
        Check::traversable($traversable);

        $this->chunkSize = $chunkSize;

        if (is_array($traversable)) {
            $traversable = new ArrayIterator($traversable);
        }

        parent::__construct($traversable);
    }

    public function rewind()
    {
        parent::rewind();
        $this->next();
        $this->key = 0;
    }

    public function next()
    {
        $this->chunk = array();
        for ($i = 0; $i < $this->chunkSize && parent::valid(); $i++) {
            $this->chunk[] = parent::current();
            parent::next();
        }
        $this->key++;
    }

    /**
     * @return \mixed[]
     */
    public function current()
    {
        return $this->chunk;
    }

    /**
     * @return integer
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return (bool) $this->chunk;
    }

}
