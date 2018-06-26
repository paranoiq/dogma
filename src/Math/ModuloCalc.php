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

class ModuloCalc
{
    use StaticClassMixin;

    /**
     * Rounds value to the closest value from give set.
     * Sets $overflow return parameter to true when maximal value is picked, but minimal value is returned.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @param int $modulus
     * @return int[]|float[]|bool[] (int|float $result, bool $overflow)
     */
    public static function roundTo($value, array $allowedValues, int $modulus): array
    {
        self::checkAllowedValues($allowedValues, $modulus);

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
     * Rounds value to first bigger or same value.
     * Sets $overflow return parameter to true when maximal value is picked, but minimal value is returned.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @param int $modulus
     * @return int[]|float[]|bool[] (int|float $result, bool $overflow)
     */
    public static function roundUpTo($value, array $allowedValues, int $modulus): array
    {
        self::checkAllowedValues($allowedValues, $modulus);

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
     * Rounds value up to first smaller or same value.
     * Cannot overflow.
     * @param int|float $value
     * @param int[]|float[] $allowedValues
     * @param int $modulus
     * @return int|float
     */
    public static function roundDownTo($value, array $allowedValues, int $modulus)
    {
        self::checkAllowedValues($allowedValues, $modulus);

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
    private static function checkAllowedValues(array $values, int $modulus): void
    {
        if ($values === []) {
            throw new InvalidArgumentException('Allowed values should not be empty.');
        }
        if (max($values) >= $modulus) {
            throw new InvalidArgumentException('All allowed values should be smaller than modulus.');
        }
    }

}
