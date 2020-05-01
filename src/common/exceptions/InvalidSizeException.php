<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function implode;
use function is_array;
use function sprintf;

class InvalidSizeException extends Exception
{

    /**
     * @param string|\Dogma\Type $type
     * @param int|int[] $actualSize
     * @param int[]|string[] $allowedSizes
     * @param \Throwable|null $previous
     */
    public function __construct($type, $actualSize, array $allowedSizes, ?\Throwable $previous = null)
    {
        if (!$allowedSizes) {
            parent::__construct(sprintf('Size parameter is not allowed on type %s.', ExceptionTypeFormatter::format($type)), $previous);
        } else {
            $sizes = implode(', ', $allowedSizes);
            if (is_array($actualSize)) {
                $actualSize = implode(',', $actualSize);
            }
            parent::__construct(sprintf('Size %s is not valid for type %s. Allowed sizes: %s.', $actualSize, ExceptionTypeFormatter::format($type), $sizes), $previous);
        }
    }

}
