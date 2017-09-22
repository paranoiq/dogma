<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity;

use Dogma\Mapping\Mapper;
use Dogma\Type;

abstract class Repository
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Type */
    private $entityType;

    /** @var \Dogma\Mapping\Mapper */
    private $mapper;

    public function __construct(Type $entityType, Mapper $mapper)
    {
        if (!$entityType->isImplementing(Entity::class)) {
            throw new \Dogma\InvalidTypeException(Entity::class, $entityType->getName());
        }
        $this->entityType = $entityType;
        $this->mapper = $mapper;
    }

    /**
     * @param \Dogma\Entity\Identity $identity
     * @throws \Dogma\Entity\EntityNotFoundException
     */
    public function get(Identity $identity): Entity
    {
        $entity = $this->find($identity);
        if (!$entity) {
            throw new \Dogma\Entity\EntityNotFoundException($identity, $this->entityType->getName());
        }
        return $entity;
    }

    public function find(Identity $identity): ?Entity
    {
        ///
        $data = [];
        ///

        if ($data) {
            return $this->mapper->map($this->entityType, $data);
        } else {
            return null;
        }
    }

}
