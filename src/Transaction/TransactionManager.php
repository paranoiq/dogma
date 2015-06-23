<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Transaction;

use Dogma\Check;

class TransactionManager
{
    use \Dogma\StrictBehaviorMixin;

    /** @var int */
    private $versionId = 0;

    /** @var \Dogma\Transaction\Transaction[] */
    private $transactions = [];

    /** @var \Dogma\Transaction\VersionAware[] */
    private $versionListeners;

    /** @var \Dogma\Transaction\TransactionAware[] */
    private $transactionListeners;

    /**
     * @param \Dogma\Transaction\VersionAware[] $versionListeners
     * @param \Dogma\Transaction\TransactionAware[] $transactionListeners
     */
    public function __construct(array $versionListeners = [], array $transactionListeners = [])
    {
        Check::itemsOfType($versionListeners, VersionAware::class);
        Check::itemsOfType($transactionListeners, TransactionAware::class);

        $this->versionListeners = $versionListeners;
        $this->transactionListeners = $transactionListeners;
    }

    /**
     * Version increments with each modification of persistent immutable entities
     */
    public function incrementVersion(): int
    {
        $this->versionId++;

        foreach ($this->versionListeners as $listener) {
            $listener->incrementVersion($this->versionId);
        }

        return $this->versionId;
    }

    /**
     * Version can be released only when all transactions are closed
     * @param int $versionId
     */
    public function releaseVersion(int $versionId): void
    {
        ///

        foreach ($this->versionListeners as $listener) {
            $listener->releaseToVersion($versionId);
        }
    }

    /**
     * Version is rolled back after a transaction is rolled back or fails
     * @param int $versionId
     */
    public function rollbackToVersion(int $versionId): void
    {
        ///

        foreach ($this->versionListeners as $listener) {
            $listener->rollbackToVersion($versionId);
        }
    }

    public function start(?string $name = null, ?IsolationLevel $isolationLevel = null): Transaction
    {
        $this->versionId++;
        $transaction = new Transaction($this, $this->versionId, $name, $isolationLevel);

        $this->transactions[$this->versionId] = $transaction;

        foreach ($this->transactionListeners as $listener) {
            $listener->start($transaction);
        }

        return $transaction;
    }

    public function commit(Transaction $transaction): void
    {
        ///

        foreach ($this->transactionListeners as $listener) {
            $listener->commit($transaction);
        }
    }

    public function rollback(Transaction $transaction): void
    {
        ///

        foreach ($this->transactionListeners as $listener) {
            $listener->rollback($transaction);
        }
    }

}
