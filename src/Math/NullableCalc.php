<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\StaticClassMixin;
use const PHP_ROUND_HALF_UP;
use function abs;
use function ceil;
use function round;
use function sqrt;

class NullableCalc
{
    use StaticClassMixin;

    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function add($a, $b)
    {
        return $a === null || $b === null ? null : $a + $b;
    }

    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function subtract($a, $b)
    {
        return $a === null || $b === null ? null : $a - $b;
    }

    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function multiply($a, $b)
    {
        return $a === null || $b === null ? null : $a * $b;
    }


    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function divide($a, $b)
    {
        return $a === null || $b === null ? null : $a / $b;
    }

    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function modulo($a, $b)
    {
        return $a === null || $b === null ? null : $a % $b;
    }

    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function power($a, $b)
    {
        return $a === null || $b === null ? null : $a ** $b;
    }

    /**
     * @param int|float|null $a
     * @param int|float|null $b
     * @return int|float|null
     */
    public static function root($a, $b)
    {
        return $a === null || $b === null ? null : $a ** (1 / $b);
    }

    /**
     * @param int|float|null $number
     * @return int|float|null
     */
    public static function sqrt($number)
    {
        return $number === null ? null : sqrt($number);
    }

    /**
     * @param int|float|null $number
     * @return int|float|null
     */
    public static function abs($number)
    {
        return $number === null ? null : abs($number);
    }

    /**
     * @param int|float|null $number
     * @return float|null
     */
    public static function round($number, int $precision = 0, int $mode = PHP_ROUND_HALF_UP): ?float
    {
        return $number === null ? null : round($number, $precision, $mode);
    }

    /**
     * @param int|float|null $number
     * @return int|float|null
     */
    public static function ceil($number): ?float
    {
        return $number === null ? null : ceil($number);
    }

    /**
     * @param int|float|null $number
     * @return float|null
     */
    public static function floor($number): ?float
    {
        return $number === null ? null : floor($number);
    }

    // group operators -------------------------------------------------------------------------------------------------

    /**
     * @param int|float|null ...$numbers
     * @return int|float|null
     */
    public static function addAll(...$numbers)
    {
        $result = 0;
        foreach ($numbers as $number) {
            if ($number === null) {
                return null;
            }
            $result += $number;
        }

        return $result;
    }

    /**
     * @param int|float|null $total
     * @param int|float|null ...$numbers
     * @return int|float|null
     */
    public static function subtractAll($total, ...$numbers)
    {
        $result = $total;
        if ($result === null) {
            return null;
        }
        foreach ($numbers as $number) {
            if ($number === null) {
                return null;
            }
            $result -= $number;
        }

        return $result;
    }

    /**
     * @param int|float|null ...$numbers
     * @return int|float|null
     */
    public static function multiplyAll(...$numbers)
    {
        $result = 1;
        foreach ($numbers as $number) {
            if ($number === null) {
                return null;
            }
            $result *= $number;
        }

        return $result;
    }

    /**
     * @param int|float|null $total
     * @param int|float|null ...$divisors
     * @return int|float|null
     */
    public static function divideByAll($total, ...$divisors)
    {
        $result = $total;
        if ($result === null) {
            return null;
        }
        foreach ($divisors as $divisor) {
            if ($divisor === null) {
                return null;
            }
            $result /= $divisor;
        }

        return $result;
    }

}
