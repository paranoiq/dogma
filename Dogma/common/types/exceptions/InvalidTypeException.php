<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class InvalidTypeException extends \Dogma\Exception
{

    /**
     * @param string|string[]
     * @param string|object
     * @param \Throwable|null
     */
    public function __construct($expectedType, $actualType, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Expected a value of type %s. %s given.', ExceptionTypeFormater::format($expectedType), ExceptionTypeFormater::format($actualType)),
            $previous
        );
    }

}
