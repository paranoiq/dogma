<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Email;

use Dogma\Web\Domain;
use Dogma\Web\Tld;

class EmailAddress
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string */
    private $address;

    public function __construct(string $address)
    {
        $this->address = $address;
    }

    public static function validate(string $address): bool
    {
        return preg_match('~^[a-z0-9-!#\$%&\'*+/=?^_`{|}\~]+(?:[.][a-z0-9-!#\$%&\'*+/=?^_`{|}\~]+)*@(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?[.])+[a-z]{2,6}$~iu', $address);
    }

    public function getDomain(): Domain
    {
        $parts = explode('@', $this->address);

        return new Domain(end($parts));
    }

    public function getTld(): Tld
    {
        $parts = explode('.', $this->address);

        return Tld::get(end($parts));
    }

}
