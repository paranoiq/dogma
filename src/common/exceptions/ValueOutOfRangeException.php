<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Throwable;

class ValueOutOfRangeException extends InvalidValueException
{

    public function __construct(int|float|string $value, int|float|null $min, int|float|null $max, ?Throwable $previous = null)
    {
        $value = ExceptionValueFormatter::format($value);

        if ($min === null) {
            Exception::__construct("Expected a value lower than $max. Value $value given.", $previous);
        } elseif ($max === null) {
            Exception::__construct("Expected a value higher than $min. Value $value given.", $previous);
        } else {
            Exception::__construct("Expected a value within the range of $min and $max. Value $value given.", $previous);
        }
    }

}
