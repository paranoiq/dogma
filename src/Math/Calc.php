<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math;

class Calc
{
    use \Dogma\StaticClassMixin;

    /**
     * @phpcsSuppress Squiz.Arrays.ArrayDeclaration.ValueNoNewline
     * @var int[]
     */
    private static $primes = [
        2, 3, 5, 7, 11, 13, 17, 19, 23, 29,
        31, 37, 41, 43, 47, 53, 59, 61, 67, 71,
        73, 79, 83, 89, 97, 101, 103, 107, 109, 113,
        127, 131, 137, 139, 149, 151, 157, 163, 167, 173,
        179, 181, 191, 193, 197, 199, 211, 223, 227, 229,
        233, 239, 241, 251, 257, 263, 269, 271, 277, 281,
        283, 293, 307, 311, 313, 317, 331, 337, 347, 349,
        353, 359, 367, 373, 379, 383, 389, 397, 401, 409,
        419, 421, 431, 433, 439, 443, 449, 457, 461, 463,
        467, 479, 487, 491, 499, 503, 509, 521, 523, 541,
    ];

    /** @var int */
    private static $lastSieved = 541;

    /** @var int */
    private static $sieve;

    /**
     * @param int $max
     * @return int[]
     */
    public static function getPrimes(int $max): array
    {
        ///

        return [];
    }

    /**
     * @param int $number
     * @return int[]
     */
    public static function factorize(int $number): array
    {
        ///

        return [];
    }

    public static function getGreatestCommonDivider(int $first, int $second): int
    {
        if (function_exists('gmp_gcd')) {
            return gmp_gcd($first, $second);
        }
        $firstFactors = self::factorize($first);
        $secondFactors = self::factorize($second);

        ///
    }

    public static function simplify(Fraction $fraction): Fraction
    {
        $gcd = self::getGreatestCommonDivider($fraction->getNumerator(), $fraction->getDenominator());

        return $gcd > 1 ? new Fraction($fraction->getNumerator() / $gcd, $fraction->getDenominator() / $gcd) : $fraction;
    }

}
