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
use Dogma\Entity\Map\EntityMap;

trait EntityProxyMixin
{

    final public function __construct(Identity $identity, EntityMap $entityMap)
    {
        $this->identity = $identity;
        $this->entityMap = $entityMap;
    }

    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    public function getEntity(): Entity
    {
        if ($this->entity === null) {
            $this->entity = $this->entityMap->get($this->identity, self::ENTITY_CLASS);
        }
        return $this->entity;
    }

}
