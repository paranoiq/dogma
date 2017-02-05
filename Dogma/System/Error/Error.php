<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System\Error;

interface Error
{

    /**
     * @return int
     */
    public function getValue(); // compat with Enum

    public function getDescription(): string;

}
