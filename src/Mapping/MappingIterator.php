<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\ArrayIterator;
use Dogma\Type;

class MappingIterator implements \Iterator
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Iterator */
    private $source;

    /** @var \Dogma\Type */
    private $type;

    /** @var \Dogma\Mapping\Mapper */
    private $mapper;

    /** @var bool */
    private $reverse;

    /** @var int */
    private $key = 0;

    /**
     * @param mixed[]|\Traversable $source
     * @param \Dogma\Type $type
     * @param \Dogma\Mapping\Mapper $mapper
     * @param bool $reverse
     */
    public function __construct($source, Type $type, Mapper $mapper, bool $reverse = false)
    {
        if (is_array($source)) {
            $source = new ArrayIterator($source);
        } elseif ($source instanceof \IteratorAggregate) {
            $source = $source->getIterator();
        }

        $this->source = $source;
        $this->type = $type;
        $this->mapper = $mapper;
        $this->reverse = $reverse;
    }

    /**
     * @throws \Exception
     */
    public function rewind(): void
    {
        $this->source->rewind();
        $this->key = 0;
    }

    public function next(): void
    {
        $this->key++;
        $this->source->next();
    }

    public function valid(): bool
    {
        return $this->source->valid();
    }

    /**
     * @return mixed|null
     */
    public function current()
    {
        if ($this->reverse) {
            return $this->mapper->reverseMap($this->type, $this->source->current());
        } else {
            return $this->mapper->map($this->type, $this->source->current());
        }
    }

    public function key(): int
    {
        return $this->key;
    }

}
