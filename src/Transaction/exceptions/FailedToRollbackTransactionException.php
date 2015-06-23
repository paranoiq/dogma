<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Transaction;

class FailedToRollbackTransactionException extends \Dogma\Exception implements \Dogma\Transaction\Exception
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Transaction\Transaction */
    private $transaction;

    public function __construct(Transaction $transaction, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Transaction %s cannot be rolled back.', $transaction->getName()), $previous);

        $this->transaction = $transaction;
    }

}
