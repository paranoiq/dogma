<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

class DomException extends \Dogma\Exception
{

    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message);

        $this->code = $code;
    }

}
