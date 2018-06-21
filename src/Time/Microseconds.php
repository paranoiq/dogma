<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\StaticClassMixin;

class Microseconds
{
    use StaticClassMixin;

    public const MINUTE = Seconds::MINUTE * 1000000;
    public const HOUR = Seconds::HOUR * 1000000;
    public const DAY = Seconds::DAY * 1000000;
    public const WEEK = Seconds::WEEK * 1000000;
    public const COMMON_YEAR = Seconds::COMMON_YEAR * 1000000;
    public const LEAP_YEAR = Seconds::LEAP_YEAR * 1000000;

}
