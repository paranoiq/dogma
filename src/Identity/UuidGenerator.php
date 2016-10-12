<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Identity;

interface UuidGenerator
{

    /**
     * Generates a 128bit UUID
     * @return string (16,binary)
     */
    public function createId(): string;

}
