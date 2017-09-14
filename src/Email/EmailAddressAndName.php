<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Email;

class EmailAddressAndName
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Email\EmailAddress */
    private $address;

    /** @var string */
    private $name;

    public function __construct(EmailAddress $address, ?string $name = null)
    {
        $this->address = $address;
        $this->name = $name;
    }

    public function getAddress(): EmailAddress
    {
        return $this->address;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function serialize(): string
    {
        return $this->name ? sprintf('"%s" <%s>', $this->name, $this->address) : $this->address;
    }

}
