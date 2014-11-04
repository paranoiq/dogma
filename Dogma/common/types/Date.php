<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

/**
 * Date class.
 *
 * @todo: strip time formats from format()
 */
class Date extends DateTime
{

    /**
     * @param string
     * @param \DateTimeZone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        parent::__construct($time, $timezone);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format('Y-m-d');
    }

}
