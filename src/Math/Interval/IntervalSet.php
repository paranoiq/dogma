<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use Dogma\Dumpable;
use Dogma\Equalable;
use IteratorAggregate;

/**
 * @template T
 * @extends IteratorAggregate<int, T>
 */
abstract class IntervalSet implements Equalable, Dumpable, IteratorAggregate, Countable, ArrayAccess
{

    /**
     * @return Interval[]
     */
    abstract public function getIntervals(): array;

    abstract public function isEmpty(): bool;

    //abstract public function containsValue(T $value): bool;

    /**
     * @return mixed|Interval
     */
    abstract public function envelope();//: Interval<T>;

    //abstract public function normalize(): IntervalSet<T>;

    //abstract public function add(IntervalSet<T> $set): IntervalSet<T>;

    //abstract public function addIntervals(Interval<T> ...$intervals): IntervalSet<T>;

    //abstract public function subtract(IntervalSet<T> $set): IntervalSet<T>;

    //abstract public function subtractIntervals(Interval<T> ...$intervals): IntervalSet<T>;

    //abstract public function intersect(IntervalSet<T> $set): IntervalSet<T>;

    //abstract public function intersect(Interval<T> ...$intervals): IntervalSet<T>;

    //abstract public function filterByLength(string $operator, int|float $length): IntervalSet<T>;

    /**
     * @return T[]|mixed
     */
    abstract public function map(callable $mapper);

    /**
     * Map and filter
     * @return self|mixed
     */
    abstract public function collect(callable $mapper);

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws BadMethodCallException
     */
    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException('Class is immutable');
    }

    /**
     * @param mixed $offset
     * @throws BadMethodCallException
     */
    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException('Class is immutable');
    }

}
