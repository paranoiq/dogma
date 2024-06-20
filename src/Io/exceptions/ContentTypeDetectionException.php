<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\Exception;
use Throwable;

class ContentTypeDetectionException extends Exception
{

    /**
     * @param mixed[]|null $error
     */
    public function __construct(string $message, ?array $error, ?Throwable $previous = null)
    {
        if ($error !== null) {
            $message .= ' ' . $error['message'];
        }

        parent::__construct($message, $previous);
    }

}
