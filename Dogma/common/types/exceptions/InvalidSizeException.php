<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class InvalidSizeException extends \Dogma\Exception
{

    /**
     * @param string|\Dogma\Type $type
     * @param int|int[] $actualSize
     * @param int[]|string[] $allowedSizes
     * @param \Throwable|null
     */
    public function __construct($type, $actualSize, array $allowedSizes, \Throwable $previous = null)
    {
        if (!$allowedSizes) {
            parent::__construct(sprintf('Size parameter is not allowed on type %s.', ExceptionTypeFormater::format($type)), $previous);
        } else {
            $sizes = implode(', ', $allowedSizes);
            if (is_array($actualSize)) {
                $actualSize = implode(',', $actualSize);
            }
            parent::__construct(sprintf('Size %s is not valid for type %s. Allowed sizes: %s.', $actualSize, ExceptionTypeFormater::format($type), $sizes), $previous);
        }
    }

}
