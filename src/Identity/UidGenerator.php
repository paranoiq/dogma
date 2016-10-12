<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Identity;

interface UidGenerator
{

    /**
     * Generates a 64bit machine unique id
     * @return int
     */
    public function createId(): int;

}
