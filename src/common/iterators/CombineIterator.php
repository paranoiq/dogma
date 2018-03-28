<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class CombineIterator implements \Iterator
{
    use StrictBehaviorMixin;

    /** @var \Iterator */
    private $keys;

    /** @var \Iterator */
    private $values;

    public function __construct(iterable $keys, iterable $values)
    {
        $this->keys = IteratorHelper::iterableToIterator($keys);
        $this->values = IteratorHelper::iterableToIterator($values);
    }

    public function rewind(): void
    {
        $this->keys->rewind();
        $this->values->rewind();
    }

    public function next(): void
    {
        $this->keys->next();
        $this->values->next();
    }

    public function valid(): bool
    {
        return $this->keys->valid() && $this->values->valid();
    }

    /**
     * @return mixed|null
     */
    public function current()
    {
        return $this->values->valid() ? $this->values->current() : null;
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return $this->keys->valid() ? $this->keys->current() : null;
    }

}
