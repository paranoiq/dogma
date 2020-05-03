<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application\Ansi;

use Dogma\Char;
use Dogma\Color\ColorCalc;
use Dogma\Color\HexColor;
use Dogma\StaticClassMixin;

class Action
{
    use StaticClassMixin;

    public const NONE = '';

    // formats
    public const COLOR = 'c'; // r|b|g...
    private const COLOR_8BIT = 'c8';
    private const COLOR_24BIT = 'c24';
    public const BG_COLOR = '-'; // r|g|b...
    private const BG_COLOR_8BIT = '-8';
    private const BG_COLOR_24BIT = '-24';
    public const INVERTED = 'I'; // bool
    public const STRONG = 's'; // bool
    public const FONT = 'f'; // italic|fraktur|0..9 (0 = default)
    public const UNDERLINED = 'u'; // 1|2
    public const OVERLINED = 'o'; // bool
    public const STRIKE = 'S'; // bool
    public const FRAMING = 'e'; // enframed|encircled
    public const BLINK = 'l'; // slow|fast
    public const HIDDEN = 'h'; // bool
    public const RESET = '/';

    // commands
    public const CURSOR_UP = '^'; // int
    public const CURSOR_DOWN = 'v'; // int
    public const CURSOR_RIGHT = '>'; // int
    public const CURSOR_LEFT = '<'; // int
    public const CURSOR_START = '<-';
    public const CURSOR_END = '->';
    public const CURSOR_COLUMN = '1'; // int
    public const CURSOR_POSITION = '1,1'; // [int, int]
    public const FILL_TO_POSITION = '~1,1'; // [int, int]
    public const CLEAR_LINE = '~';
    public const CLEAR_RIGHT = '~>';
    public const CLEAR_LEFT = '~<';
    public const CLEAR_UP = '~^';
    public const CLEAR_DOWN = '~v';
    public const CLEAR_ALL = '~~';
    public const CLEAR_BUFFER = '~~~';
    public const SCROLL_UP = '@^'; // int
    public const SCROLL_DOWN = '@v'; // int
    public const TERMINAL_INPUT = 't'; // int|string (key code|key name)
    public const ASCII = 'a'; // int|string (value|name)
    public const GET_POSITION = '?';

    // macros
    public const INTERLACED = 'j';
    public const NEGATIVE = 'n';
    public const TRANSFORM_CASE = 'T';

    private const STARTS = [
        self::COLOR => [
            'r' => '[31m', 'g' => '[32m', 'b' => '[34m', 'w' => '[37m',
            'c' => '[36m', 'm' => '[35m', 'y' => '[33m', 'k' => '[30m',
            'R' => '[91m', 'G' => '[92m', 'B' => '[94m', 'W' => '[97m',
            'C' => '[96m', 'M' => '[95m', 'Y' => '[93m', 'K' => '[90m',
        ],
        self::COLOR_8BIT => '[38;5;%dm',
        self::COLOR_24BIT => '[38;2;%d;%d;%dm',
        self::BG_COLOR => [
            'r' => '[41m', 'g' => '[42m', 'b' => '[44m', 'w' => '[47m',
            'c' => '[46m', 'm' => '[45m', 'y' => '[43m', 'k' => '[40m',
            'R' => '[101m', 'G' => '[102m', 'B' => '[104m', 'W' => '[107m',
            'C' => '[106m', 'M' => '[105m', 'Y' => '[103m', 'K' => '[100m',
        ],
        self::BG_COLOR_8BIT => '[48;5;%dm',
        self::BG_COLOR_24BIT => '[48;2;%d;%d;%dm',
        self::INVERTED => '[7m',
        self::STRONG => '[1m',
        self::FONT => ['italic' => '[3m', 'fraktur' => '20m', 0 => '[23,10m', 1 => '[11m', 2 => '[12m', 3 => '[13m', 4 => '[14m', 5 => '[15m', 6 => '[16m', 7 => '[17m', 8 => '[18m', 9 => '[19m'],
        self::UNDERLINED => [1 => '[4m', 2 => '[21m'],
        self::OVERLINED => '[53m',
        self::STRIKE => '[9m',
        self::FRAMING => ['enframed' => '[51m', 'encircled' => '[52m'],
        self::BLINK => ['slow' => '[5m', 'fast' => '[6m'],
        self::HIDDEN => '[8m',
        self::RESET => '[0m',

        self::CURSOR_UP => '[%dA',
        self::CURSOR_DOWN => '[%dB',
        self::CURSOR_RIGHT => '[%dC',
        self::CURSOR_LEFT => '[%dD',
        self::CURSOR_COLUMN => '[%dG',
        self::CURSOR_POSITION => '[%d;%dH',
        self::FILL_TO_POSITION => '[%d;%df',
        self::CLEAR_LINE => '[2K',
        self::CLEAR_RIGHT => '[0K',
        self::CLEAR_LEFT => '[1K',
        self::CLEAR_UP => '[1J',
        self::CLEAR_DOWN => '[0J',
        self::CLEAR_ALL => '[2J',
        self::CLEAR_BUFFER => '[3J',
        self::SCROLL_UP => '[%dS',
        self::SCROLL_DOWN => '[%dT',
        self::TERMINAL_INPUT => '',
        self::ASCII => '',
        self::GET_POSITION => '[6n',
    ];

    private const ENDS = [
        self::COLOR => '[39m',
        self::BG_COLOR => '[49m',
        self::INVERTED => '[27m',
        self::STRONG => '[22m',
        self::FONT => '[10m',
        self::UNDERLINED => '[24m',
        self::OVERLINED => '[55m',
        self::STRIKE => '[29m',
        self::FRAMING => '[54m',
        self::BLINK => '[25m',
        self::HIDDEN => '[28m',
    ];

    public static function start(string $action, $value): string
    {
        if ($action === self::COLOR || $action === self::BG_COLOR) {
            [$action, $value] = self::resolveColor($action, $value);
        }

        $pattern = self::STARTS[$action];

        if (is_array($pattern)) {
            return Char::ESC . $pattern[$value];
        } elseif (strpos($pattern, '%') !== false) {
            $params = is_array($value) ? $value : [$value];

            return Char::ESC . sprintf($pattern, ...$params);
        } else {
            return Char::ESC . $pattern;
        }
    }

    /**
     * @param string $action
     * @param string $value
     * @return string[]|int[]|int[][]
     */
    private static function resolveColor(string $action, string $value): array
    {
        if ($value[0] === '%') {
            [$r, $g, $b] = str_split(substr($value, 1));
            $value = 16 + 36 * $r + 6 * $g + $b;
            $action = $action === self::COLOR ? self::COLOR_8BIT : self::BG_COLOR_8BIT;
        } elseif ($value[0] === '*') {
            $n = substr($value, 1);
            $value = 232 + $n;
            $action = $action === self::COLOR ? self::COLOR_8BIT : self::BG_COLOR_8BIT;
        } elseif ($value[0] === '#') {
            $hex = substr($value, 1);
            $value = ColorCalc::hexToRgb($hex);
            $action = $action === self::COLOR ? self::COLOR_24BIT : self::BG_COLOR_24BIT;
        } elseif ($value[0] === '&') {
            $name = substr($value, 1);
            $hex = HexColor::getValueByName($name);
            $value = ColorCalc::hexToRgb($hex);
            $action = $action === self::COLOR ? self::COLOR_24BIT : self::BG_COLOR_24BIT;
        }

        return [$action, $value];
    }

    public static function end(string $action): ?string
    {
        $pattern = self::ENDS[$action] ?? null;

        return  $pattern ? Char::ESC . $pattern : null;
    }

}
