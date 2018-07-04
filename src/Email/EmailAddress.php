<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Email;

use Dogma\StrictBehaviorMixin;
use function sprintf;
use function strtolower;

class EmailAddress
{
    use StrictBehaviorMixin;

    /** @var string */
    private $name;

    /** @var string */
    private $address;

    public function __construct(string $address, ?string $name = null)
    {
        $this->address = strtolower($address);
        $this->name = $name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name ? sprintf('"%s" <%s>', $this->name, $this->address) : $this->address;
    }

}
