<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

class FloatCalc
{
    use \Dogma\StaticClassMixin;

    public const EPSILON = 0.0000000000001;

    public static function equals(float $first, float $second, float $epsilon = self::EPSILON): bool
    {
        return abs($first - $second) < $epsilon;
    }

}
