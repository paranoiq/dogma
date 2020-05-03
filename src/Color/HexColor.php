<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Color;

use Dogma\Check;
use Dogma\Enum\PartialStringEnum;
use Dogma\InvalidValueException;
use function bin2hex;
use function chr;
use function hex2bin;
use function in_array;
use function ord;
use function str_replace;
use function str_split;
use function strlen;
use function strtolower;
use function strtoupper;
use function substr;

class HexColor extends PartialStringEnum
{

    // ANSI 4bit colors
    public const WHITE = 'ffffff'; // W
    public const SILVER = 'c0c0c0'; // w
    public const RED = 'ff0000'; // R
    public const MAROON = '800000'; // r
    public const LIME = '00ff00'; // G
    public const GREEN = '008000'; // g
    public const BLUE = '0000ff'; // B
    public const NAVY = '000080'; // b
    public const CYAN = '00ffff'; // C
    public const TEAL = '008080'; // c
    public const MAGENTA = 'ff00ff'; // M
    public const PURPLE = '800080'; // m
    public const YELLOW = 'ffff00'; // Y
    public const OLIVE = '808000'; // y
    public const GRAY = '808080'; // K
    public const BLACK = '000000'; // k

    public const AQUA = '00ffff'; // alias
    public const FUCHSIA = 'ff00ff'; // alias

    // red
    public const LIGHTSALMON = 'ffa07a';
    public const SALMON = 'fa8072';
    public const DARKSALMON = 'e9967a';
    public const LIGHTCORAL = 'f08080';
    public const INDIANRED = 'cd5c5c';
    public const CRIMSON = 'dc143c';
    public const FIREBRICK = 'b22222';
    public const DARKRED = '8b0000';

    // orange
    public const CORAL = 'ff7f50';
    public const TOMATO = 'ff6347';
    public const ORANGERED = 'ff4500';
    public const GOLD = 'ffd700';
    public const ORANGE = 'ffa500';
    public const DARKORANGE = 'ff8c00';

    // yellow
    public const LIGHTYELLOW = 'ffffe0';
    public const LEMONCHIFFON = 'fffacd';
    public const LIGHTGOLDENRODYELLOW = 'fafad2';
    public const PAPAYAWHIP = 'ffefd5';
    public const MOCCASIN = 'ffe4b5';
    public const PEACHPUFF = 'ffdab9';
    public const PALEGOLDENROD = 'eee8aa';
    public const KHAKI = 'f0e68c';
    public const DARKKHAKI = 'bdb76b';

    // green
    public const LAWNGREEN = '7cfc00';
    public const CHARTREUSE = '7fff00';
    public const LIMEGREEN = '32cd32';
    public const FORESTGREEN = '228b22';
    public const DARKGREEN = '006400';
    public const GREENYELLOW = 'adff2f';
    public const YELLOWGREEN = '9acd32';
    public const SPRINGGREEN = '00ff7f';
    public const MEDIUMSPRINGGREEN = '00fa9a';
    public const LIGHTGREEN = '90ee90';
    public const PALEGREEN = '98fb98';
    public const DARKSEAGREEN = '8fbc8f';
    public const MEDIUMSEAGREEN = '3cb371';
    public const SEAGREEN = '2e8b57';
    public const DARKOLIVEGREEN = '556b2f';
    public const OLIVEDRAB = '6b8e23';

    // cyan
    public const LIGHTCYAN = 'e0ffff';
    public const AQUAMARINE = '7fffd4';
    public const MEDIUMAQUAMARINE = '66cdaa';
    public const PALETURQUOISE = 'afeeee';
    public const TURQUOISE = '40e0d0';
    public const MEDIUMTURQUOISE = '48d1cc';
    public const DARKTURQUOISE = '00ced1';
    public const LIGHTSEAGREEN = '20b2aa';
    public const CADETBLUE = '5f9ea0';
    public const DARKCYAN = '008b8b';

    // blue
    public const POWDERBLUE = 'b0e0e6';
    public const LIGHTBLUE = 'add8e6';
    public const LIGHTSKYBLUE = '87cefa';
    public const SKYBLUE = '87ceeb';
    public const DEEPSKYBLUE = '00bfff';
    public const LIGHTSTEELBLUE = 'b0c4de';
    public const DODGERBLUE = '1e90ff';
    public const CORNFLOWERBLUE = '6495ed';
    public const STEELBLUE = '4682b4';
    public const ROYALBLUE = '4169e1';
    public const MEDIUMBLUE = '0000cd';
    public const DARKBLUE = '00008b';
    public const MIDNIGHTBLUE = '191970';
    public const MEDIUMSLATEBLUE = '7b68ee';
    public const SLATEBLUE = '6a5acd';
    public const DARKSLATEBLUE = '483d8b';

    // purple
    public const LAVENDER = 'e6e6fa';
    public const THISTLE = 'd8bfd8';
    public const PLUM = 'dda0dd';
    public const VIOLET = 'ee82ee';
    public const ORCHID = 'da70d6';
    public const MEDIUMORCHID = 'ba55d3';
    public const MEDIUMPURPLE = '9370db';
    public const BLUEVIOLET = '8a2be2';
    public const DARKVIOLET = '9400d3';
    public const DARKORCHID = '9932cc';
    public const DARKMAGENTA = '8b008b';
    public const INDIGO = '4b0082';

    // pink
    public const PINK = 'ffc0cb';
    public const LIGHTPINK = 'ffb6c1';
    public const HOTPINK = 'ff69b4';
    public const DEEPPINK = 'ff1493';
    public const PALEVIOLETRED = 'db7093';
    public const MEDIUMVIOLETRED = 'c71585';

    // white(ish)
    public const SNOW = 'fffafa';
    public const HONEYDEW = 'f0fff0';
    public const MINTCREAM = 'f5fffa';
    public const AZURE = 'f0ffff';
    public const ALICEBLUE = 'f0f8ff';
    public const GHOSTWHITE = 'f8f8ff';
    public const WHITESMOKE = 'f5f5f5';
    public const SEASHELL = 'fff5ee';
    public const BEIGE = 'f5f5dc';
    public const OLDLACE = 'fdf5e6';
    public const FLORALWHITE = 'fffaf0';
    public const IVORY = 'fffff0';
    public const ANTIQUEWHITE = 'faebd7';
    public const LINEN = 'faf0e6';
    public const LAVENDERBLUSH = 'fff0f5';
    public const MISTYROSE = 'ffe4e1';

    // gray/black
    public const GAINSBORO = 'dcdcdc';
    public const LIGHTGRAY = 'd3d3d3';
    public const DARKGRAY = 'a9a9a9';
    public const DIMGRAY = '696969';
    public const LIGHTSLATEGRAY = '778899';
    public const SLATEGRAY = '708090';
    public const DARKSLATEGRAY = '2f4f4f';

    // brown
    public const CORNSILK = 'fff8dc';
    public const BLANCHEDALMOND = 'ffebcd';
    public const BISQUE = 'ffe4c4';
    public const NAVAJOWHITE = 'ffdead';
    public const WHEAT = 'f5deb3';
    public const BURLYWOOD = 'deb887';
    public const TAN = 'd2b48c';
    public const ROSYBROWN = 'bc8f8f';
    public const SANDYBROWN = 'f4a460';
    public const GOLDENROD = 'daa520';
    public const PERU = 'cd853f';
    public const CHOCOLATE = 'd2691e';
    public const SADDLEBROWN = '8b4513';
    public const SIENNA = 'a0522d';
    public const BROWN = 'a52a2a';

    private const ANSI_4BIT = [
        'R' => self::RED,
        'r' => self::MAROON,
        'G' => self::LIME,
        'g' => self::GREEN,
        'B' => self::BLUE,
        'b' => self::NAVY,
        'C' => self::CYAN,
        'c' => self::TEAL,
        'M' => self::MAGENTA,
        'm' => self::PURPLE,
        'Y' => self::YELLOW,
        'y' => self::OLIVE,
        'K' => self::GRAY,
        'k' => self::BLACK,
        'W' => self::WHITE,
        'w' => self::SILVER,
    ];

    private const ANSI_GRAY = [
        '000000', '080808', '121212', '1c1c1c', '262626', '303030', '3a3a3a', '444444', '4e4e4e',
        '585858', '626262', '6c6c6c', '767676', '808080', '8a8a8a', '949494', '9e9e9e', 'a8a8a8',
        'b2b2b2', 'bcbcbc', 'c6c6c6', 'd0d0d0', 'dadada', 'e4e4e4', 'eeeeee', 'ffffff',
    ];

    private const ANSI_216_COMPONENTS = ['00', '5f', '87', 'af', 'd7', 'ff'];

    /** @var string */
    private $value;

    // constructors ----------------------------------------------------------------------------------------------------

    public static function getValueByName(string $name): string
    {
        $name = str_replace([' ', '_', '-'], '', strtoupper($name));
        $values = self::getAllowedValues();
        if (!isset($values[$name])) {
            throw new InvalidValueException($name, self::class);
        }

        return $values[$name];
    }

    public static function fromName(string $name): self
    {
        return new static(self::getValueByName($name));
    }

    public static function fromShort(string $value): self
    {
        if ($value[0] === '#') {
            $value = substr($value, 1);
        }
        if (strlen($value) !== 3) {
            throw new InvalidValueException($value, self::class);
        }

        return new static($value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2]);
    }

    public static function fromAnsi4bit(string $code): self
    {
        if (!isset(self::ANSI_4BIT[$code])) {
            throw new InvalidValueException($code, self::class);
        }

        return new static(self::ANSI_4BIT[$code]);
    }

    public static function fromAnsi216(int $r, int $g, int $b): self
    {
        Check::range($r, 0, 5);
        Check::range($r, 0, 5);
        Check::range($r, 0, 5);

        $value = self::ANSI_216_COMPONENTS[$r] . self::ANSI_216_COMPONENTS[$g] . self::ANSI_216_COMPONENTS[$b];

        return new static($value);
    }

    public static function fromAnsiGray(int $intensity): self
    {
        Check::range($intensity, 0, 25);

        return new static(self::ANSI_GRAY[$intensity]);
    }

    public static function fromRgb(int $r, int $g, int $b): self
    {
        Check::range($r, 0, 255);
        Check::range($g, 0, 255);
        Check::range($b, 0, 255);

        return new static(bin2hex(chr($r)) . bin2hex(chr($g)) . bin2hex(chr($b)));
    }

    /**
     * @param float $h (range 0..1)
     * @param float $s (range 0..1)
     * @param float $l (range 0..1)
     * @return self
     */
    public static function fromHsl(float $h, float $s, float $l): self
    {
        return self::fromRgb(...ColorCalc::hslToRgb($h, $s, $l));
    }

    /**
     * @param float $h (range 0..1)
     * @param float $s (range 0..1)
     * @param float $v (range 0..1)
     * @return self
     */
    public static function fromHsv(float $h, float $s, float $v): self
    {
        return self::fromRgb(...ColorCalc::hsvToRgb($h, $s, $v));
    }

    // interface stuff -------------------------------------------------------------------------------------------------

    public static function validateValue(string &$value): bool
    {
        if ($value[0] === '#') {
            $value = substr($value, 1);
        }
        $value = strtolower($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return '[0-9a-f]{6}';
    }

    // -----------------------------------------------------------------------------------------------------------------

    public function isAnsi4bit(): bool
    {
        return in_array($this->value, self::ANSI_4BIT, true);
    }

    public function isAnsiGray(): bool
    {
        return in_array($this->value, self::ANSI_GRAY, true);
    }

    public function isAnsi216(): bool
    {
        [$r, $g, $b] = str_split($this->value, 2);

        return in_array($r, self::ANSI_216_COMPONENTS, true)
            && in_array($g, self::ANSI_216_COMPONENTS, true)
            && in_array($b, self::ANSI_216_COMPONENTS, true);
    }

    public function isAnsi8bit(): bool
    {
        return $this->isAnsi4bit() || $this->isAnsiGray() || $this->isAnsi216();
    }

    /**
     * @return int[]
     */
    public function getRgb(): array
    {
        [$r, $g, $b] = str_split($this->value, 2);

        return [ord(hex2bin($r)), ord(hex2bin($g)), ord(hex2bin($b))];
    }

    /**
     * @return float[] (range 0..1)
     */
    public function getHsl(): array
    {
        [$r, $g, $b] = $this->getRgb();

        return ColorCalc::rgbToHsl($r, $g, $b);
    }

    /**
     * @return float[] (range 0..1)
     */
    public function getHsv(): array
    {
        [$r, $g, $b] = $this->getRgb();

        return ColorCalc::rgbToHsv($r, $g, $b);
    }

}

