<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application\Ansi;

/**
 * https://en.wikipedia.org/wiki/Braille_Patterns
 * http://xahlee.info/comp/unicode_drawing_shapes.html
 * http://xahlee.info/comp/unicode_dingbats.html
 */
class ProgressBar
{

    public const SHADED_BLOCK           = ' ░▒▓█';
    public const BAR_LEFT               = ' ▏▎▍▌▋▊▉█';
    public const BAR_BOTTOM             = ' ▁▂▃▄▅▆▇█';
    public const QUARTERS_LEFT_BOTTOM   = ' ▖▌▙█';
    public const QUARTERS_LEFT_TOP      = ' ▘▌▛█';
    public const QUARTERS_BOTTOM_LEFT   = ' ▖▄▙█';
    public const QUARTERS_TOP_LEFT      = ' ▘▀▛█';
    public const DOTS6_LEFT_BOTTOM      = ' ⠄⠆⠇⠧⠷⠿';
    public const DOTS6_LEFT_TOP         = ' ⠁⠃⠇⠏⠟⠿';
    public const DOTS6_BOTTOM_LEFT      = ' ⠄⠤⠦⠶⠷⠿';
    public const DOTS6_TOP_LEFT         = ' ⠁⠉⠋⠛⠟⠿';
    public const DOTS8_LEFT_BOTTOM      = ' ⡀⡄⡆⡇⣇⣧⣷⣿';
    public const DOTS8_LEFT_TOP         = ' ⠁⠃⠇⡇⡏⡟⡿⣿';
    public const DOTS8_BOTTOM_LEFT      = ' ⡀⣀⣄⣤⣦⣶⣷⣿';
    public const DOTS8_TOP_LEFT         = ' ⠁⠉⠋⠛⠟⠿⡿⣿';
    public const DIGRAM_TOP             = '𝌅𝌄𝌁⚍⚌';
    public const DIGRAM_TOP_REVERSE     = '⚌⚍𝌁𝌄𝌅';
    public const TETRAGRAM_TOP          = '𝍖𝍕𝍔𝍑𝍎𝍅𝌼𝌡𝌆';
    public const TETRAGRAM_TOP_REVERSE  = '𝌆𝌡𝌼𝍅𝍎𝍑𝍔𝍕𝍖';

}
