<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

use Dogma\Check;
use Dogma\Math\Sequence\Prime;
use Dogma\StaticClassMixin;
use function abs;
use function array_product;
use function ceil;
use function floor;
use function min;
use function range;

class IntCalc
{
    use StaticClassMixin;

    /**
     * @param int $number
     * @return int[]
     */
    public static function binaryComponents(int $number): array
    {
        $components = [];
        $e = 0;
        do {
            $c = 1 << $e;
            if (($number & $c) !== 0) {
                $components[] = $c;
            }
        } while ($e++ < 64);

        return $components;
    }

    public static function floor(float $number): int
    {
        return (int) floor($number);
    }

    public static function ceil(float $number): int
    {
        return (int) ceil($number);
    }

    public static function roundTo(int $number, int $multiple): int
    {
        $up = self::roundUpTo($number, $multiple);
        $down = self::roundDownTo($number, $multiple);

        return abs($up - $number) > abs($number - $down) ? $down : $up;
    }

    public static function roundDownTo(int $number, int $multiple): int
    {
        $multiple = abs($multiple);

        return (int) (floor($number / $multiple) * $multiple);
    }

    public static function roundUpTo(int $number, int $multiple): int
    {
        $multiple = abs($multiple);

        return (int) (ceil($number / $multiple) * $multiple);
    }

    /**
     * Maps number from range 0.0 - 1.0 to integers 0 to $max with same probability for each integer
     *
     * @param float $number (range 0..1)
     * @param int $max
     * @return int
     */
    public static function mapTo(float $number, int $max): int
    {
        return (int) min(floor($number * ($max + 1)), $max);
    }

    /**
     * @param int $n
     * @return int|float
     */
    public static function factorial(int $n)
    {
        $sign = $n < 0 ? -1 : 1;
        $n = $sign * $n;

        return $sign * ($n > 1 ? array_product(range(2, $n)) : 1);
    }

    /**
     * @param int $number
     * @return int[]
     */
    public static function factorize(int $number): array
    {
        Check::range($number, 1);
        if ($number === 1) {
            return [1];
        }

        $possibleFactors = Prime::getUntil($number);

        $factors = [];
        foreach ($possibleFactors as $factor) {
            while (($number % $factor) === 0) {
                $factors[] = $factor;
                $number /= $factor;
            }
        }

        return $factors;
    }

    public static function greatestCommonDivider(int $a, int $b): int
    {
        $next = $a % $b;

        return $next === 0 ? $b : self::greatestCommonDivider($b, $next);
    }

    public static function leastCommonMultiple(int $a, int $b): int
    {
        return $a * ($b / self::greatestCommonDivider($a, $b));
    }

    public static function binomialCoefficient(int $n, int $k): int
    {
        $result = 1;

        // since C(n, k) = C(n, n-k)
        if ($k > $n - $k) {
            $k = $n - $k;
        }

        // calculate value of [n*(n-1)*---*(n-k+1)] / [k*(k-1)*---*1]
        for ($i = 0; $i < $k; ++$i) {
            $result *= ($n - $i);
            $result /= ($i + 1);
        }

        /** @var int $result */
        $result = $result;

        return $result;
    }

}
