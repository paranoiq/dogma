<?php

namespace Dogma\Tests\Mapping;

class ExportableTestClass implements \Dogma\Mapping\Type\Exportable
{
    private $one;
    private $two;

    /**
     * @param int $one
     * @param float $two
     */
    public function __construct($one, $two)
    {
        $this->one = $one;
        $this->two = $two;
    }

    public function export()
    {
        return [
            'one' => $this->one,
            'two' => $this->two,
        ];
    }
}

class OuterTestClass implements \Dogma\Mapping\Type\Exportable
{
    private $three;
    private $four;

    /**
     * @param \Dogma\Tests\Mapping\ExportableTestClass $three
     * @param string $four
     */
    public function __construct(ExportableTestClass $three, $four)
    {
        $this->three = $three;
        $this->four = $four;
    }

    public function export()
    {
        return [
            'three' => $this->three,
            'four' => $this->four,
        ];
    }
}
