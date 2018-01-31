<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: iu

namespace Dogma\Web;

class Domain
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function validate(string $name): bool
    {
        return preg_match('~^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?[.])+[a-z]{2,6}$~iu', $name);
    }

    public function getTld(): Tld
    {
        $parts = explode('.', $this->name);

        return Tld::get(end($parts));
    }

}
