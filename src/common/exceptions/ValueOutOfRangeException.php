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

class ValueOutOfRangeException extends InvalidValueException
{

    /**
     * @param int|float $value
     * @param int|float|null $min
     * @param int|float|null $max
     * @param \Throwable|null $previous
     */
    public function __construct($value, $min, $max, ?\Throwable $previous = null)
    {
        if ($min === null) {
            Exception::__construct(
                sprintf('Expected a value lower than %s. Value %s given.', $max, ExceptionValueFormatter::format($value)),
                $previous
            );
        } elseif ($max === null) {
            Exception::__construct(
                sprintf('Expected a value higher than %s. Value %s given.', $min, ExceptionValueFormatter::format($value)),
                $previous
            );
        } else {
            Exception::__construct(
                sprintf('Expected a value within the range of %s and %s. Value %s given.', $min, $max, ExceptionValueFormatter::format($value)),
                $previous
            );
        }
    }

}
