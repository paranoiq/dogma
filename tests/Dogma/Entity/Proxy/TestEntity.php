<?php

namespace Dogma\Test\Entity\Proxy;

use Dogma\Entity\Identity;
use Dogma\Time\DateTime;

class TestEntity implements \Dogma\Entity\Entity
{

    public function getIdentity(): Identity
    {
        return null;
    }

    /**
     * @param \Dogma\Time\DateTime $one
     * @param mixed $two
     * @return string
     */
    public function one(DateTime $one, $two): string
    {
        return 'x';
    }

    protected function getY(): string
    {
        return 'y';
    }

}
