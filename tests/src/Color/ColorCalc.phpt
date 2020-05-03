<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Color\ColorCalc;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$n = 0;
for ($r = 0; $r <= 255; $r++) {
    for ($g = 0; $g <= 255; $g++) {
        for ($b = 0; $b <= 255; $b++) {
            if (($n % 67) === 0) {
                $hsl = ColorCalc::rgbToHsl($r, $g, $b);
                Assert::same(ColorCalc::hslToRgb(...$hsl), [$r, $g, $b]);
            }
            $n++;
        }
    }
}

$n = 0;
for ($r = 0; $r <= 255; $r++) {
    for ($g = 0; $g <= 255; $g++) {
        for ($b = 0; $b <= 255; $b++) {
            if (($n % 67) === 0) {
                $hsl = ColorCalc::rgbToHsv($r, $g, $b);
                Assert::same(ColorCalc::hsvToRgb(...$hsl), [$r, $g, $b]);
            }
            $n++;
        }
    }
}
