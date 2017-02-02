<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class ValueOutOfBoundsException extends \Dogma\ValueOutOfRangeException
{

    /**
     * @param int|float|string
     * @param string|\Dogma\Type
     * @param \Throwable|null
     */
    public function __construct($value, $type, \Throwable $previous = null)
    {
        Exception::__construct(
            sprintf('Value %s cannot fit to data type %s.', ExceptionValueFormater::format($value), ExceptionTypeFormater::format($type)),
            $previous
        );
    }

}
