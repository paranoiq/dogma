<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Decimal;

use Dogma\Math\MathException;

class DecimalArithmeticException extends MathException
{

    /** @var Decimal|null */
    private $first;

    /** @var Decimal|null */
    private $second;

    public function __construct(string $message, ?Decimal $first = null, ?Decimal $second = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->first = $first;
        $this->second = $second;
    }

    public function getFirst(): ?Decimal
    {
        return $this->first;
    }

    public function getSecond(): ?Decimal
    {
        return $this->second;
    }

}
