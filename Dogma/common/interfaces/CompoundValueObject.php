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
 * Value object consisting of two or more values.
 */
interface CompoundValueObject extends ValueObject
{

    /**
     * Returns value components in order of constructor parameters.
     * @return array
     */
    public function toArray();

}
