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
use Dogma\Transaction\TransactionManager;
use Dogma\Type;

class RepositoryContainer
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Transaction\TransactionManager */
    private $transactionManager;

    /** @var \Dogma\Mapping\Mapper */
    private $mapper;

    /** @var \Dogma\Entity\Repository[] */
    private $repositories;

    /**
     * @param \Dogma\Transaction\TransactionManager $transactionManager
     * @param \Dogma\Mapping\Mapper $mapper
     * @param \Dogma\Entity\Repository[] $repositories
     */
    public function __construct(TransactionManager $transactionManager, Mapper $mapper, array $repositories)
    {
        $this->transactionManager = $transactionManager;
        $this->mapper = $mapper;
        $this->repositories = $repositories;
    }

    /**
     * @param \Dogma\Transaction\TransactionManager $transactionManager
     * @param \Dogma\Mapping\Mapper $mapper
     * @param string[] $classes
     * @return self
     */
    public static function create(TransactionManager $transactionManager, Mapper $mapper, array $classes): self
    {
        $repositories = [];
        foreach ($classes as $entityClass => $repositoryClass) {
            $repositories[$entityClass] = new $repositoryClass(Type::get($entityClass), $mapper);
        }
        return new self($transactionManager, $mapper, $repositories);
    }

    public function getRepository(string $entityClass): Repository
    {
        if (!isset($this->repositories[$entityClass])) {
            throw new \Dogma\Entity\RepositoryNotFoundException($entityClass);
        }
        return $this->repositories[$entityClass];
    }

    public function get(Identity $identity, ?string $entityClass = null): Entity
    {
        /// $entityClass = $entityClass ?: $identity->getClass();

        $repository = $this->getRepository($entityClass);

        return $repository->get($identity);
    }

    public function getById(string $class, int $id): Entity
    {
        $identity = Identity::get($class, $id);

        return $this->get($identity);
    }

}
