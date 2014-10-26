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
 * Value object consisting of just one value.
 */
interface SimpleValueObject extends ValueObject
{


    /**
     * Returns string value of the value object.
     * @return string
     */
    public function __toString();

}
