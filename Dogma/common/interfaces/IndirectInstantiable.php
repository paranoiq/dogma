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
 * Object which cannot be instantiated through keyword 'new'.
 */
interface IndirectInstantiable
{

    /**
     * Returns new instance of the called class.
     * @param scalar
     * @return static
     */
    public static function getInstance($value);

}
