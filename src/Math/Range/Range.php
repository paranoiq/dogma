<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Range;

interface Range /*<T, RangeSet<T>>*/
{

    // queries ---------------------------------------------------------------------------------------------------------

    public function format(): string;

    /**
     * @return mixed
     */
    public function getStart();

    /**
     * @return mixed
     */
    public function getEnd();

    public function isEmpty(): bool;

    //public function equals(self $range): bool;

    //public function containsValue(T $value): bool;

    //public function contains(self $range): bool;

    //public function intersects(self $range): bool;

    //public function touches(self $range): bool;

    // actions ---------------------------------------------------------------------------------------------------------

    /**
     * @param int $parts
     * @return mixed|\Dogma\Math\Range\RangeSet
     */
    public function split(int $parts);//: RangeSet<T>;

    /**
     * @param array<T> $rangeStarts
     * @return mixed|\Dogma\Math\Range\RangeSet
     */
    public function splitBy(array $rangeStarts);//: RangeSet<T>;

    // A1****A2****B1****B2 -> [A1, B2]
    //public function envelope(self ...$items): self;

    // A and B
    // A1----B1****A2----B2 -> [B1, A2]
    // A1----A2    B1----B2 -> [empty]
    //public function intersect(self ...$items): self;

    // A or B
    // A1****B1****A2****B2 -> {[A1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    //public function union(self ...$items): RangeSet<T>;

    // A xor B
    // A1****B1----A2****B2 -> {[A1, A2], [B1, B2]}
    // A1****A2    B1****B2 -> {[A1, A2], [B1, B2]}
    //public function difference(self ...$items): RangeSet<T>;

    // A minus B
    // A1****B1----A2----B2 -> {[A1, B1]}
    // A1****A2    B1----B2 -> {[A1, A2]}
    //public function subtract(self ...$items): RangeSet<T>;

    /**
     * @return mixed|\Dogma\Math\Range\RangeSet
     */
    public function invert();//: RangeSet<T>;

    // static ----------------------------------------------------------------------------------------------------------

    /**
     * @param \Dogma\Math\Range\Range<T> ...$items
     * @return \Dogma\Math\Range\Range<T>[][]|int[][] ($ident => ($range, $count))
     */
    //public static function countOverlaps(self ...$items): array;

    /**
     * O(n log n)
     * @param \Dogma\Math\Range\Range<T> ...$items
     * @return \Dogma\Math\Range\Range<T>[]
     */
    //public static function explodeOverlaps(self ...$items): array;

    /**
     * @param self[] $ranges
     * @return self[]
     */
    public static function sort(array $ranges): array;

    /**
     * @param self[] $ranges
     * @return self[]
     */
    public static function sortByStart(array $ranges): array;

}
