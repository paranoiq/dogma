<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Transaction;

interface TransactionAware
{

    /**
     * Returns true when supports nested transactions / savepoints
     */
    public function supportsNestedTransactions(): bool;

    /**
     * @param \Dogma\Transaction\Transaction $transaction
     * @throws \Dogma\Transaction\FailedToStartTransactionException
     */
    public function start(Transaction $transaction): void;

    /**
     * @param \Dogma\Transaction\Transaction $transaction
     * @throws \Dogma\Transaction\FailedToCommitTransactionException
     */
    public function commit(Transaction $transaction): void;

    /**
     * @param \Dogma\Transaction\Transaction $transaction
     * @throws \Dogma\Transaction\FailedToRollbackTransactionException
     */
    public function rollback(Transaction $transaction): void;

}
