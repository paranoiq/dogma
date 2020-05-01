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

class InvalidEncodingException extends InvalidValueException
{

    /**
     * @param string $value
     * @param string $expectedEncoding
     * @param \Throwable|null $previous
     */
    public function __construct(string $value, string $expectedEncoding, ?\Throwable $previous = null)
    {
        Exception::__construct(
            sprintf('Value %s is not a valid %s string.', ExceptionValueFormatter::format($value), $expectedEncoding),
            $previous
        );
    }

}
