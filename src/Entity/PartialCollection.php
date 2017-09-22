<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity;

class PartialCollection extends \Dogma\Collection
{

    /**
     * @param string $accepted
     * @param object[] $items
     */
    public function __construct(string $accepted, array $items = [])
    {
        parent::__construct($accepted, $items);

        ///
    }

}
