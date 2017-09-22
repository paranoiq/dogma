<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Proxy;

use Dogma\Entity\Entity;
use Dogma\Entity\Identity;

/**
 * Proxy responsibilities:
 * - lazy reference (empty proxy seeks for entity only when needed)
 * - immutable decorator (ensures all methods are immutable even when not written to be)
 * - maybe monade (passes empty value, until a non-proxyfied value is needed)
 */
interface EntityProxy extends \Dogma\Entity\Entity
{

    public function getEntity(): Entity;

    public function getIdentity(): Identity;

}
