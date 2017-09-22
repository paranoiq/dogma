<?php

namespace Dogma\Math\Decimal;

class DecimalArithmeticException extends \Dogma\Math\MathException
{

    /** @var \Dogma\Math\Decimal\Decimal */
    private $first;

    /** @var \Dogma\Math\Decimal\Decimal|null */
    private $second;

    public function __construct(string $message, Decimal $first, ?Decimal $second = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->first = $first;
        $this->second = $second;
    }

    public function getFirst(): Decimal
    {
        return $this->first;
    }

    public function getSecond(): ?Decimal
    {
        return $this->second;
    }

}
