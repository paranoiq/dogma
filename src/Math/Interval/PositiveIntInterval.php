<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\StrictBehaviorMixin;
use const PHP_INT_MAX;

class PositiveIntInterval extends IntInterval
{
    use StrictBehaviorMixin;

    public const MIN = 1;
    public const MAX = PHP_INT_MAX;

}
