<?php

namespace Dogma\Tests\Mapping;

class ExportableTestClass implements \Dogma\Mapping\Type\Exportable
{

    /** @var int */
    private $one;

    /** @var float */
    private $two;

    /**
     * @param int $one
     * @param float $two
     */
    public function __construct(int $one, float $two)
    {
        $this->one = $one;
        $this->two = $two;
    }

    /**
     * @return mixed[]
     */
    public function export(): array
    {
        return [
            'one' => $this->one,
            'two' => $this->two,
        ];
    }

}
