<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function get_class;
use function gettype;
use function interface_exists;
use function is_object;
use function sprintf;

class InvalidInterfaceException extends InvalidTypeException
{

    /**
     * @param string $expectedInterface
     * @param mixed $value
     * @param \Throwable|null $previous
     */
    public function __construct(string $expectedInterface, $value, ?\Throwable $previous = null)
    {
        if (is_object($value)) {
            $type = get_class($value);
        } else {
            $type = gettype($value);
        }
        $class = true;
        if (interface_exists($expectedInterface)) {
            $class = false;
        }
        if ($class) {
            Exception::__construct(sprintf('Expected an instance of %s. %s given.', $expectedInterface, $type), $previous);
        } else {
            Exception::__construct(sprintf('Expected an object implementing %s. %s given.', $expectedInterface, $type), $previous);
        }
    }

}
