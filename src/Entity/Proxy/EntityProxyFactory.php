<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Proxy;

use Dogma\Entity\Identity;
use Dogma\Entity\Map\EntityMap;
use Dogma\Type;

class EntityProxyFactory
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Entity\Map\EntityMap */
    private $entityMap;

    public function __construct(EntityMap $entityMap)
    {
        $this->entityMap = $entityMap;
    }

    public function createProxy(Identity $identity, Type $type): EntityProxy
    {
        $proxyClass = $type->getName() . EntityProxyBuilder::CLASS_NAME_SUFFIX;

        return new $proxyClass($identity, $this->entityMap);
    }

}
