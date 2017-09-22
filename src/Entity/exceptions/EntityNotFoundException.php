<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity;

class EntityNotFoundException extends \Dogma\Exception implements \Dogma\Entity\Exception
{

    public function __construct(Identity $identity, string $entityClass, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Entity id %d of class \'%s\' was not found.', $identity->getId(), $entityClass), $previous);
    }

}
