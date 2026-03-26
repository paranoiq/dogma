<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application;

use Dogma\Color\Color;
use Dogma\Str;
use function hexdec;
use function ltrim;
use function preg_replace;
use function str_pad;
use function strlen;
use function strtolower;
use function substr;
use function user_error;
use const E_USER_NOTICE;
use const STR_PAD_RIGHT;

final class Ansi
{

    public const WHITE = 'W';
    public const LGRAY = 'w';
    public const DGRAY = 'K';
    public const BLACK = 'k';
    public const LRED = 'R';
    public const DRED = 'r';
    public const LGREEN = 'G';
    public const DGREEN = 'g';
    public const LBLUE = 'B';
    public const DBLUE = 'b';
    public const LCYAN = 'C';
    public const DCYAN = 'c';
    public const LMAGENTA = 'M';
    public const DMAGENTA = 'm';
    public const LYELLOW = 'Y';
    public const DYELLOW = 'y';

    public const RESET_FORMAT = "\e[0m";
    public const UP = "\e[A";
    public const DELETE_ROW = "\e[2K";

    public const GET_CURSOR_POSITION = "\e[6n";
    public const SAVE_CURSOR_POSITION = "\e[s";
    public const RESTORE_CURSOR_POSITION = "\e[u";
    public const SHOW_CURSOR = "\e[?25h";
    public const HIDE_CURSOR = "\e[?25l";

    public const ERASE_CURSOR_TO_END = 0;
    public const ERASE_CURSOR_TO_START = 1;
    public const ERASE_ALL = 2;
    public const ERASE_ALL_AND_BUFFER = 3;

    public static bool $off = false;

    public static string $default = self::LGRAY;

    /** @var array<string> */
    private static array $fg = [
        self::WHITE => '1;37',
        self::LGRAY => '0;37',
        self::DGRAY => '1;30',
        self::BLACK => '0;30',

        self::DRED => '0;31',
        self::LRED => '1;31',
        self::DGREEN => '0;32',
        self::LGREEN => '1;32',
        self::DBLUE => '0;34',
        self::LBLUE => '1;34',

        self::DCYAN => '0;36',
        self::LCYAN => '1;36',
        self::DMAGENTA => '0;35',
        self::LMAGENTA => '1;35',
        self::DYELLOW => '0;33',
        self::LYELLOW => '1;33',
    ];

    /** @var array<string, string> */
    private static array $bg = [
        self::LGRAY => '47',
        self::BLACK => '40',

        self::DRED => '41',
        self::DGREEN => '42',
        self::DBLUE => '44',

        self::DYELLOW => '43',
        self::DMAGENTA => '45',
        self::DCYAN => '46',

        // aliases
        self::WHITE => '47',
        self::DGRAY => '40',

        self::LRED => '41',
        self::LGREEN => '42',
        self::LBLUE => '44',

        self::LYELLOW => '43',
        self::LMAGENTA => '45',
        self::LCYAN => '46',
    ];

    public static function color(string $string, ?string $color = null, ?string $background = null): string
    {
        $string = (string) $string;

        if (self::$off || ($background === null && $color === self::$default)) {
            return $string;
        }

        $out = '';
        if ($color !== null && isset(self::$fg[$color])) {
            $out .= "\e[" . self::$fg[$color] . 'm';
        }
        if ($background !== null && isset(self::$bg[$background])) {
            $out .= "\e[" . self::$bg[$background] . 'm';
        }

        $end = $background === null
            ? "\e[" . self::$fg[self::$default] . "m" // does not reset background
            : "\e[0m";

        return $out . $string . $end;
    }

    public static function colorStart(string $color): string
    {
        return "\e[" . self::$fg[$color] . "m";
    }

    public static function rgb(int|float|string $string, ?string $color, ?string $background = null): string
    {
        $string = (string) $string;

        $color = $color ? strtolower(ltrim($color, "#")) : Color::NAMED_COLORS[$background ? 'black' : 'silver'];
        $color = Color::NAMED_COLORS[$color] ?? $color;
        if (strlen($color) === 3) {
            $r = hexdec($color[0] . $color[0]);
            $g = hexdec($color[1] . $color[1]);
            $b = hexdec($color[2] . $color[2]);
        } elseif (strlen($color) === 6) {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        } else {
            user_error("Invalid color: #{$color}", E_USER_NOTICE);

            return $string;
        }
        if ($background === null) {
            return "\e[38;2;{$r};{$g};{$b}m{$string}\e[0m";
        }

        $background = strtolower(ltrim($background, "#"));
        $background = Color::NAMED_COLORS[$background] ?? $background;
        if (strlen($background) === 3) {
            $rb = hexdec($background[0] . $background[0]);
            $gb = hexdec($background[1] . $background[1]);
            $bb = hexdec($background[2] . $background[2]);
        } elseif (strlen($background) === 6) {
            $rb = hexdec(substr($background, 0, 2));
            $gb = hexdec(substr($background, 2, 2));
            $bb = hexdec(substr($background, 4, 2));
        } else {
            user_error("Invalid background color: #{$background}", E_USER_NOTICE);

            return $string;
        }

        return "\e[38;2;{$r};{$g};{$b}m\e[48;2;{$rb};{$gb};{$bb}m{$string}\e[0m";
    }

    public static function between(int|float|string $string, string $color, string $after, string $background = self::BLACK): string
    {
        if (self::$off || $color === $after) {
            return (string) $string;
        }

        if ($background === self::BLACK) {
            return "\e[" . self::$fg[$color] . 'm' . $string . "\e[" . self::$fg[$after] . 'm';
        } else {
            return "\e[" . self::$fg[$color] . "m\e[" . self::$bg[$background] . 'm' . $string . "\e[" . self::$fg[$after] . "m\e[" . self::$bg[$background] . 'm';
        }
    }

    public static function background(int|float|string $string, string $background): string
    {
        return self::color($string, null, $background);
    }

    public static function length(int|float|string $string, string $encoding = 'utf-8'): int
    {
        return Str::length(self::removeFormats($string), $encoding);
    }

    public static function pad(int|float|string $string, int $length, string $with = ' ', int $type = STR_PAD_RIGHT): string
    {
        $original = self::removeFormats($string);

        return str_pad($string, $length + strlen($string) - strlen($original) + 1, $with, $type);
    }

    public static function removeFormats(string $string): string
    {
        return (string) preg_replace('/\\x1B\\[[^m]+m/U', '', $string);
    }

    public static function bold(bool $on = true): string
    {
        return $on ? "\e[1m" : "\e[22m";
    }

    public static function faint(bool $on = true): string
    {
        return $on ? "\e[2m" : "\e[22m";
    }

    public static function italic(bool $on = true): string
    {
        return $on ? "\e[3m" : "\e[23m";
    }

    public static function underline(bool $on = true): string
    {
        return $on ? "\e[4m" : "\e[24m";
    }

    public static function overline(bool $on = true): string
    {
        return $on ? "\e[53m" : "\e[55m";
    }

    public static function blink(bool $on = true): string
    {
        return $on ? "\e[5m" : "\e[25m";
    }

    public static function hide(bool $on = true): string
    {
        return $on ? "\e[8m" : "\e[28m";
    }

    public static function strike(bool $on = true): string
    {
        return $on ? "\e[9m" : "\e[29m";
    }

    // shortcuts -------------------------------------------------------------------------------------------------------

    public static function white(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::WHITE, $background);
    }

    public static function lgray(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LGRAY, $background);
    }

    public static function gray(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DGRAY, $background);
    }

    public static function black(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::BLACK, $background);
    }

    public static function red(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LRED, $background);
    }

    public static function lred(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LRED, $background);
    }

    public static function green(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DRED, $background);
    }

    public static function lgreen(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LGREEN, $background);
    }

    public static function blue(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DGREEN, $background);
    }

    public static function lblue(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LBLUE, $background);
    }

    public static function cyan(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DBLUE, $background);
    }

    public static function lcyan(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LCYAN, $background);
    }

    public static function magenta(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DCYAN, $background);
    }

    public static function lmagenta(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LMAGENTA, $background);
    }

    public static function yellow(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DMAGENTA, $background);
    }

    public static function lyellow(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::LYELLOW, $background);
    }

    public static function dyellow(int|float|string $string, ?string $background = null): string
    {
        return self::color($string, self::DYELLOW, $background);
    }

    // other commands --------------------------------------------------------------------------------------------------

    public static function cursorUp(int $n = 1): string
    {
        return "\e[{$n}A";
    }

    public static function cursorDown(int $n = 1): string
    {
        return "\e[{$n}B";
    }

    public static function cursorForward(int $n = 1): string
    {
        return "\e[{$n}C";
    }

    public static function cursorBack(int $n = 1): string
    {
        return "\e[{$n}D";
    }

    public static function cursorNextLine(int $n = 1): string
    {
        return "\e[{$n}E";
    }

    public static function cursorPreviousLine(int $n = 1): string
    {
        return "\e[{$n}F";
    }

    public static function cursorColumn(int $n = 1): string
    {
        return "\e[{$n}G";
    }

    public static function cursorPosition(int $n = 1, int $m = 1): string
    {
        return "\e[{$n};{$m}H";
    }

    /**
     * @param self::ERASE_* $n
     */
    public static function eraseScreen(int $n = 0): string
    {
        return "\e[{$n}J";
    }

    /**
     * @param self::ERASE_* $n
     */
    public static function eraseLine(int $n = 0): string
    {
        return "\e[{$n}K";
    }

    public static function scrollUp(int $n = 1): string
    {
        return "\e[{$n}S";
    }

    public static function scrollDown(int $n = 1): string
    {
        return "\e[{$n}T";
    }

    public static function hvPosition(int $n = 1, int $m = 1): string
    {
        return "\e[{$n};{$m}f";
    }

    public static function getCursorPosition(): string
    {
        return "\e[6n";
    }

    public static function saveCursorPosition(): string
    {
        return "\e[s";
    }

    public static function restoreCursorPosition(): string
    {
        return "\e[u";
    }

    public static function showCursor(): string
    {
        return "\e[?25h";
    }

    public static function hideCursor(): string
    {
        return "\e[?25l";
    }

    public static function setWindowTitle(string $title): string
    {
        return "\e]0;{$title}\e\\";
    }

    public static function link(string $text, string $url): string
    {
        return "\e]8;;{$url}\e\\{$text}\e]8;;\e\\";
    }

}
