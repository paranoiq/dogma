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

class InvalidTypeException extends Exception
{

    /**
     * @param string|string[] $expectedType
     * @param string|object $actualType
     * @param \Throwable|null $previous
     */
    public function __construct($expectedType, $actualType, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('Expected a value of type %s. %s given.', ExceptionTypeFormatter::format($expectedType), ExceptionTypeFormatter::format($actualType)),
            $previous
        );
    }

}
