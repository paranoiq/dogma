<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function count;

/**
 * Cycles through given iterators. Iterators should return same number of items.
 */
class RoundRobinIterator implements \Iterator
{
    use StrictBehaviorMixin;

    /** @var \Iterator[] */
    private $iterators;

    /** @var int */
    private $current;

    /** @var int */
    private $key;

    public function __construct(iterable ...$iterables)
    {
        $this->iterators = [];
        foreach ($iterables as $iterable) {
            $this->iterators[] = IteratorHelper::iterableToIterator($iterable);
        }
        $this->current = 0;
        $this->key = 0;
    }

    public function rewind(): void
    {
        foreach ($this->iterators as $iterator) {
            $iterator->rewind();
        }
        $this->current = 0;
        $this->key = 0;
    }

    public function next(): void
    {
        $this->current++;
        $this->key++;
        if ($this->current >= count($this->iterators)) {
            $this->current = 0;
        }
        if ($this->current === 0) {
            foreach ($this->iterators as $iterator) {
                $iterator->next();
            }
        }
    }

    public function valid(): bool
    {
        if ($this->current === 0) {
            $valid = 0;
            $invalid = 0;
            foreach ($this->iterators as $iterator) {
                if ($iterator->valid()) {
                    $valid++;
                } else {
                    $invalid++;
                }
            }
            if ($valid === 0) {
                return false;
            } elseif ($invalid === 0) {
                return true;
            } else {
                throw new UnevenIteratorSourcesException('Given iterators do not return same amount of items.');
            }
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    public function current()
    {
        return $this->iterators[$this->current]->current();
    }

    /**
     * @return mixed|null
     */
    public function key()
    {
        return $this->key;
    }

}
