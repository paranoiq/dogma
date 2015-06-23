<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Transaction;

class Transaction implements \Dogma\NonIterable
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonIterableMixin;

    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var \Dogma\Transaction\IsolationLevel|null */
    private $isolationLevel;

    /** @var \Dogma\Transaction\TransactionManager */
    private $manager;

    /**
     * @param \Dogma\Transaction\TransactionManager $manager
     * @param int $id
     * @param string|null $name
     * @param \DOgma\Transaction\IsolationLevel|null $isolationLevel
     */
    final public function __construct(
        TransactionManager $manager,
        int $id,
        ?string $name = null,
        ?IsolationLevel $isolationLevel = null
    ) {
        $this->manager = $manager;
        $this->id = $id;
        $this->name = $name;
        $this->isolationLevel = $isolationLevel;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name ?: ('tx' . $this->id);
    }

    public function getIsolationLevel(): ?IsolationLevel
    {
        return $this->isolationLevel;
    }

    public function commit(): void
    {
        ///
    }

    public function rollback(): void
    {
        ///
    }

}
