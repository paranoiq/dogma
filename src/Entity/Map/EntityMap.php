<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Map;

use Dogma\Entity\Entity;
use Dogma\Entity\Identity;

class EntityMap
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Entity\Entity[][] ($className => ($identityHash => $entity)) */
    private $entities;

    public function add(Entity $entity): void
    {
        $hash = spl_object_hash($entity->getIdentity());
        $className = get_class($entity);

        if (!isset($this->entities[$className][$hash])) {
            $this->entities[$className][$hash] = $entity;
        }
    }

    public function contains(Identity $identity, string $className): bool
    {
        $hash = spl_object_hash($identity);

        return isset($this->entities[$className][$hash]);
    }

    /**
     * @return \Dogma\Entity\Entity[][]
     */
    public function getAll(): array
    {
        return $this->entities;
    }

    /**
     * @param string $className
     * @return \Dogma\Entity\Entity[]
     */
    public function getByClass(string $className): array
    {
        if (isset($this->entities[$className])) {
            return $this->entities[$className];
        } else {
            return [];
        }
    }

    public function find(Identity $identity, string $className): ?Entity
    {
        $hash = spl_object_hash($identity);
        if (isset($this->entities[$className][$hash])) {
            return $this->entities[$className][$hash];
        } else {
            return null;
        }
    }

}
