<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Color;

use Dogma\StaticClassMixin;
use function bin2hex;
use function chr;
use function hex2bin;
use function ord;
use function str_split;
use function strlen;
use function substr;

class ColorCalc
{
    use StaticClassMixin;

    /**
     * @param string $hex (3 or 6 chars, no #)
     * @return int[] (range 0..255)
     */
    public static function hexToRgb(string $hex): array
    {
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        [$r, $g, $b] = str_split($hex, 2);

        return [ord(hex2bin($r)), ord(hex2bin($g)), ord(hex2bin($b))];
    }

    /**
     * @param int $r (range 0..255)
     * @param int $g (range 0..255)
     * @param int $b (range 0..255)
     * @return string (6 chars, no #)
     */
    public static function rgbToHex(int $r, int $g, int $b): string
    {
        return bin2hex(chr($r)) . bin2hex(chr($g)) . bin2hex(chr($b));
    }

    /**
     * @param float $h (range 0..1)
     * @param float $s (range 0..1)
     * @param float $l (range 0..1)
     * @return int[] (range 0..255)
     */
    public static function hslToRgb(float $h, float $s, float $l): array
    {
        if ($s === 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = self::hue2rgb($p, $q, $h + 1/3);
            $g = self::hue2rgb($p, $q, $h);
            $b = self::hue2rgb($p, $q, $h - 1/3);
        }

        return [
            (int) floor($r * 255.999999),
            (int) floor($g * 255.999999),
            (int) floor($b * 255.999999),
        ];
    }

    /**
     * @param int $r (range 0..255)
     * @param int $g (range 0..255)
     * @param int $b (range 0..255)
     * @return float[] (range 0..1)
     */
    public static function rgbToHsl(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = $s = 0.0;
        } else {
            $d = $max - $min;
            $s = $l >= 0.5 ? $d / (2 - ($max + $min)) : $d / ($max + $min);

            if ($max === $r) {
                $h = ($g - $b) / $d + 0;
            } elseif ($max === $g) {
                $h = ($b - $r) / $d + 2;
            } else {
                $h = ($r - $g) / $d + 4;
            }

            $h /= 6;
        }

        return [$h, $s, $l];
    }

    /**
     * @param float $h (range 0..1)
     * @param float $s (range 0..1)
     * @param float $v (range 0..1)
     * @return int[] (range 0..255)
     */
    public static function hsvToRgb(float $h, float $s, float $v): array
    {
        $h *= 6;
        $i = floor($h);
        $f = $h - $i;
        $m = $v * (1 - $s);
        $n = $v * (1 - $s * $f);
        $k = $v * (1 - $s * (1 - $f));

        switch ($i) {
            case 0: [$r, $g, $b] = [$v, $k, $m]; break;
            case 1: [$r, $g, $b] = [$n, $v, $m]; break;
            case 2: [$r, $g, $b] = [$m, $v, $k]; break;
            case 3: [$r, $g, $b] = [$m, $n, $v]; break;
            case 4: [$r, $g, $b] = [$k, $m, $v]; break;
            default: [$r, $g, $b] = [$v, $m, $n]; break;
        }

        return [
            (int) floor($r * 255.999999),
            (int) floor($g * 255.999999),
            (int) floor($b * 255.999999)
        ];
    }

    /**
     * @param int $r (range 0..255)
     * @param int $g (range 0..255)
     * @param int $b (range 0..255)
     * @return float[] (range 0..1)
     */
    public static function rgbToHsv(int $r, int $g, int $b): array
    {
        $r /= 255;
        $g /= 255;
        $b /= 255;
        $min = min($r, $g, $b);
        $max = max($r, $g, $b);
        $v = $max;

        if ($max === $min) {
            $h = $s = 0;
        } else {
            $d = $max - $min;
            $s = $d / $max;
            $dr = (($max - $r) / 6 + $d / 2) / $d;
            $dg = (($max - $g) / 6 + $d / 2) / $d;
            $db = (($max - $b) / 6 + $d / 2) / $d;

            if ($r === $max) {
                $h = $db - $dg;
            } elseif ($g === $max) {
                $h = 1/3 + $dr - $db;
            } else {
                $h = 2/3 + $dg - $dr;
            }

            if ($h < 0) {
                $h++;
            } elseif ($h > 1) {
                $h--;
            }
        }

        return [$h, $s, $v];
    }

    private static function hue2rgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t++;
        } elseif ($t > 1) {
            $t--;
        }

        if ($t < 1/6) {
            return $p + ($q - $p) * 6 * $t;
        } elseif ($t < 1/2) {
            return $q;
        } elseif ($t < 2/3) {
            return $p + ($q - $p) * (2/3 - $t) * 6;
        } else {
            return $p;
        }
    }

}
