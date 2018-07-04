<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function sprintf;

class ValueOutOfBoundsException extends ValueOutOfRangeException
{

    /**
     * @param int|float|string $value
     * @param string|\Dogma\Type $type
     * @param \Throwable|null $previous
     */
    public function __construct($value, $type, ?\Throwable $previous = null)
    {
        Exception::__construct(
            sprintf('Value %s cannot fit to data type %s.', ExceptionValueFormatter::format($value), ExceptionTypeFormatter::format($type)),
            $previous
        );
    }

}
