<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class InvalidRegularExpressionException extends \Dogma\InvalidValueException
{

    /**
     * @param mixed $regexp
     * @param \Throwable|null $previous
     */
    public function __construct($regexp, ?\Throwable $previous = null)
    {
        \Dogma\Exception::__construct(
            sprintf('Value \'%s\' is not a valid regular expression.', $regexp),
            $previous
        );
    }

}
