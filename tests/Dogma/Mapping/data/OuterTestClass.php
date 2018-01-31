<?php

namespace Dogma\Tests\Mapping;

class OuterTestClass implements \Dogma\Mapping\Type\Exportable
{

    /** @var \Dogma\Tests\Mapping\ExportableTestClass */
    private $three;

    /** @var string */
    private $four;

    /**
     * @param \Dogma\Tests\Mapping\ExportableTestClass $three
     * @param string $four
     */
    public function __construct(ExportableTestClass $three, string $four)
    {
        $this->three = $three;
        $this->four = $four;
    }

    /**
     * @return mixed[]
     */
    public function export(): array
    {
        return [
            'three' => $this->three,
            'four' => $this->four,
        ];
    }

}
