<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

class ConstantTimeProvider implements TimeProvider
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Time\DateTime */
    private $dateTime;

    public function __construct(DateTime $dateTime = null)
    {
        if (!$dateTime) {
            $dateTime = new DateTime();
        }
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

}
