<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Dumpable;
use Dogma\Equalable;
use IteratorAggregate;

/**
 * @template T
 * @template I of Interval
 * @extends IteratorAggregate<int, I>
 */
interface IntervalSet extends Equalable, Dumpable, IteratorAggregate
{

    /**
     * @return array<I>
     */
    public function getIntervals(): array;

    public function isEmpty(): bool;

    //public function containsValue(T $value): bool;

    /**
     * @return I
     */
    public function envelope(): Interval;

    //public function normalize(): IntervalSet<T>;

    //public function add(IntervalSet<T> $set): IntervalSet<T>;

    //public function addIntervals(Interval<T> ...$intervals): IntervalSet<T>;

    //public function subtract(IntervalSet<T> $set): IntervalSet<T>;

    //public function subtractIntervals(Interval<T> ...$intervals): IntervalSet<T>;

    //public function intersect(IntervalSet<T> $set): IntervalSet<T>;

    //public function intersect(Interval<T> ...$intervals): IntervalSet<T>;

    //public function filterByLength(string $operator, int|float $length): IntervalSet<T>;

    public function map(callable $mapper): static;

    /**
     * Map and filter
     */
    public function collect(callable $mapper): static;

}
