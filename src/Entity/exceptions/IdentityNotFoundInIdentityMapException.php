<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity;

use Dogma\Entity\Map\IdentityMap;
use Dogma\ExceptionValueFormater;

class IdentityNotFoundInIdentityMapException extends \Dogma\Exception implements \Dogma\Entity\Exception
{

    public function __construct(Identity $identity, IdentityMap $identityMap)
    {
        parent::__construct(sprintf(
            '%s was not found in %s.',
            ExceptionValueFormater::format($identity),
            ExceptionValueFormater::format($identityMap)
        ));
    }

}
