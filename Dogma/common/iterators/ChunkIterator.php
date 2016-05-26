<?php

namespace Dogma;

class ChunkIterator extends \IteratorIterator
{
    use \Dogma\StrictBehaviorMixin;

    /** @var int */
    private $chunkSize;

    /** @var int */
    private $key;

    /** @var mixed[] */
    private $chunk;

    /**
     * @param mixed[]|\Traversable $traversable
     * @param int $chunkSize
     */
    public function __construct($traversable, int $chunkSize)
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
     * @return mixed[]
     */
    public function current(): array
    {
        return $this->chunk;
    }

    public function key(): int
    {
        return $this->key;
    }

    public function valid(): bool
    {
        return (bool) $this->chunk;
    }

}
