<?php

namespace Dogma\Test\Database\SqlGenerator;

use Dogma\Geolocation\Position;
use Dogma\People\Address\StreetAddress;

class TestingType
{

    /** @var int (64) */
    private $id;

    /** @var string (50) */
    private $name;

    /** @var int (16) */
    private $val;

    /** @var \Dogma\People\Address\StreetAddress */
    private $address;

    /** @var \Dogma\Geolocation\Position */
    private $position;

    /**
     * @param int (64u) $id
     * @param int (16,signed) $val
     * @param string (50) $name
     * @param \Dogma\People\Address\StreetAddress $address
     * @param \Dogma\Geolocation\Position $position
     */
    public function __construct(int $id, int $val, string $name, StreetAddress $address, Position $position)
    {
        $this->id = $id;
        $this->val = $val;
        $this->name = $name;
        $this->address = $address;
        $this->position = $position;
    }

}
