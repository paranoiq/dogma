<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Map;

use Dogma\Entity\Identity;

class IdentityMap
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Entity\Identity[][] ($className => ($id => $identity)) */
    protected $idMap = [];

    public function add(Identity $identity): void
    {
        $className = get_class($identity);

        $this->idMap[$className][$identity->getId()] = $identity;
    }

    public function contains(Identity $identity): bool
    {
        $className = get_class($identity);

        return isset($this->idMap[$className][$identity->getId()]);
    }

    /**
     * @return \Dogma\Entity\Identity[]
     */
    public function getAll(): array
    {
        $identities = [];
        foreach ($this->idMap as $map) {
            $identities = array_merge($identities, $map);
        }
        return $identities;
    }

    /**
     * @param string $className
     * @return \Dogma\Entity\Identity[]
     */
    public function getByClass(string $className): array
    {
        $identities = [];
        if (isset($this->idMap[$className])) {
            $identities = array_merge($identities, $this->idMap[$className]);
        }
        return $identities;
    }

    public function findById(string $className, int $id): ?Identity
    {
        if (isset($this->idMap[$className][$id])) {
            return $this->idMap[$className][$id];
        } else {
            return null;
        }
    }

}
