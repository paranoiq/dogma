<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Phone;

class PhoneNumber
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string */
    private $number;

    /** @var \Dogma\People\Phone\PhonePrefix|null */
    private $prefix;

    public function __construct(string $number)
    {
        ///
        $this->number = $number;
    }

    public function getCountryPrefix(): ?PhonePrefix
    {
        return $this->prefix;
    }

}
