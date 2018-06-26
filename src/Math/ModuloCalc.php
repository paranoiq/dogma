<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\InvalidArgumentException;
use Dogma\StaticClassMixin;

/**
 * Calculations in modular arithmetic.
 */
class ModuloCalc
{
    use StaticClassMixin;

    /**
     * Calculates all differences between given values.
     * @param int[]|float[] $values
     * @param int $modulus
     * @return int[]|float[]
     */
    public static function differences(array $values, int $modulus): array
    {
        self::checkValues($values, $modulus);

        sort($values);
        $values[] = $values[0] + $modulus;

        $differences = [];
        $max = count($values) - 2;
        foreach ($values as $i => $value) {
            $differences[] = $values[$i + 1] - $value;
            if ($i === $max) {
                break;
            }
        }

        return $differences;
    }

    /**
     * Rounds value to the closest value from given set.
     * Sets overflow to true when maximal value is picked, but minimal value is returned.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @param int $modulus
     * @return int[]|float[]|bool[] (int|float $result, bool $overflow)
     */
    public static function roundTo($value, array $allowedValues, int $modulus): array
    {
        self::checkValues($allowedValues, $modulus);

        sort($allowedValues);
        if (in_array(0, $allowedValues)) {
            $allowedValues[] = $modulus;
        }

        $pickedValue = null;
        foreach ($allowedValues as $allowedValue) {
            if ($value < $allowedValue) {
                if ($allowedValue - $value < $value - $pickedValue) {
                    $pickedValue = $allowedValue;
                }
                break;
            }
            $pickedValue = $allowedValue;
        }

        if ($pickedValue === $modulus) {
            $overflow = true;
            $pickedValue = 0;
        } else {
            $overflow = false;
        }

        return [$pickedValue, $overflow];
    }

    /**
     * Rounds value to first bigger or same value from given set.
     * Sets overflow to true when maximal value is picked, but minimal value is returned.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @param int $modulus
     * @return int[]|float[]|bool[] (int|float $result, bool $overflow)
     */
    public static function roundUpTo($value, array $allowedValues, int $modulus): array
    {
        self::checkValues($allowedValues, $modulus);

        sort($allowedValues);
        if (in_array(0, $allowedValues)) {
            $allowedValues[] = $modulus;
        }

        $pickedValue = null;
        foreach ($allowedValues as $allowedValue) {
            if ($value <= $allowedValue) {
                $pickedValue = $allowedValue;
                break;
            }
        }

        if ($pickedValue === $modulus) {
            $overflow = true;
            $pickedValue = 0;
        } else {
            $overflow = false;
        }

        return [$pickedValue, $overflow];
    }

    /**
     * Rounds value up to first smaller or same value from given set.
     * Cannot overflow.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @param int $modulus
     * @return int|float
     */
    public static function roundDownTo($value, array $allowedValues, int $modulus)
    {
        self::checkValues($allowedValues, $modulus);

        rsort($allowedValues);
        $pickedValue = null;
        foreach ($allowedValues as $allowedValue) {
            if ($value >= $allowedValue) {
                $pickedValue = $allowedValue;
                break;
            }
        }

        return $pickedValue;
    }

    /**
     * @param int[] $values
     * @param int $modulus
     */
    private static function checkValues(array &$values, int $modulus): void
    {
        if ($values === []) {
            throw new InvalidArgumentException('Values should not be empty.');
        }
        if (max($values) >= $modulus || min($values) < 0) {
            throw new InvalidArgumentException('All values should be smaller than modulus.');
        }
        $values = array_values($values);
    }

}
