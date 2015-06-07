<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

/**
 * Immutable list of fixed number of parameters
 */
class Tuple implements \Countable, \IteratorAggregate, \ArrayAccess
{
    use StrictBehaviorMixin;
    use ImmutableArrayAccessMixin;

    /** @var mixed[] */
    private $items;

    /**
     * @param mixed[] $items
     */
    public function __construct(...$items)
    {
        $this->items = $items;
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return \Dogma\ArrayIterator
     */
    public function getIterator()
    {
        return new \Dogma\ArrayIterator($this->items);
    }

    /**
     * @return mixed[]
     */
    public function toArray()
    {
        return $this->items;
    }

}
