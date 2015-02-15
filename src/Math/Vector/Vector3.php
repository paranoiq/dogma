<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Vector;

use Dogma\Math\FloatCalc;

class Vector3
{
    use \Dogma\StrictBehaviorMixin;

    /** @var float */
    private $x;

    /** @var float */
    private $y;

    /** @var float */
    private $z;

    public function __construct(float $x, float $y, float $z)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @return float[] ($latRad, $lonRad)
     */
    public static function normalVectorToRadians(float $x, float $y, float $z): array
    {
        $lonRad = atan2($y, $x);
        $hyp = sqrt($x * $x + $y * $y);
        $latRad = atan2($z, $hyp);

        return [$latRad, $lonRad];
    }

    /**
     * @param float $latRad
     * @param float $lonRad
     * @return float[] ($x, $y, $z)
     */
    public static function radiansToNormalVector(float $latRad, float $lonRad): array
    {
        $x = cos($latRad) * cos($lonRad);
        $y = cos($latRad) * sin($lonRad);
        $z = sin($latRad);

        return [$x, $y, $z];
    }

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     * @return float[]
     */
    public static function normalize(float $x, float $y, float $z): array
    {
        $size = abs(sqrt($x * $x + $y * $y + $z * $z));

        if (!FloatCalc::equals($size, 1.0)) {
            $x = $x / $size;
            $y = $y / $size;
            $z = $z / $size;
        }

        return [$x, $y, $z];
    }

    public static function dotProduct(float $ax, float $ay, float $az, float $bx, float $by, float $bz): float
    {
        $dotProduct = $ax * $bx + $ay * $by + $az * $bz;

        // fixes rounding error on 16th place, which can cause a NAN later
        if ($dotProduct > 1.0) {
            $dotProduct = 1.0;
        } elseif ($dotProduct < -1.0) {
            $dotProduct = -1.0;
        }

        return $dotProduct;
    }

}
