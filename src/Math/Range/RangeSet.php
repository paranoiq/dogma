<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Range;

interface RangeSet /*<T>*/
{

    /**
     * @return \Dogma\Math\Range\Range[]
     */
    public function getRanges(): array;

    public function isEmpty(): bool;

    //public function containsValue(T $value): bool;

    /**
     * @return mixed|\Dogma\Math\Range\Range
     */
    public function envelope();//: Range<T>;

}
