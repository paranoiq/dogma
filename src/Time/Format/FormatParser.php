<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use Dogma\Time\DateTimeFormatter;

class FormatParser
{
    use \Dogma\StrictBehaviorMixin;

    private $characterGroups = [
        DateTimeFormatter::YEAR => DateTimeFormatter::YEAR,
        DateTimeFormatter::YEAR_SHORT => DateTimeFormatter::YEAR,
        DateTimeFormatter::LEAP_YEAR => DateTimeFormatter::YEAR,

        DateTimeFormatter::DAY_OF_YEAR => DateTimeFormatter::DAY_OF_YEAR,
        DateTimeFormatter::DAY_OF_YEAR_INDEX => DateTimeFormatter::DAY_OF_YEAR,

        DateTimeFormatter::QUARTER => DateTimeFormatter::QUARTER,

        DateTimeFormatter::MONTH_LZ => DateTimeFormatter::MONTH,
        DateTimeFormatter::MONTH => DateTimeFormatter::MONTH,
        DateTimeFormatter::MONTH_NAME => DateTimeFormatter::MONTH,
        DateTimeFormatter::MONTH_NAME_SHORT => DateTimeFormatter::MONTH,

        DateTimeFormatter::DAYS_IN_MONTH => DateTimeFormatter::DAYS_IN_MONTH,

        DateTimeFormatter::WEEK_OF_YEAR => DateTimeFormatter::WEEK_OF_YEAR,

        DateTimeFormatter::ISO_WEEK_YEAR => DateTimeFormatter::ISO_WEEK_YEAR,

        DateTimeFormatter::DAY_OF_WEEK => DateTimeFormatter::DAY_OF_WEEK,
        DateTimeFormatter::DAY_OF_WEEK_INDEX => DateTimeFormatter::DAY_OF_WEEK,
        DateTimeFormatter::DAY_OF_WEEK_NAME => DateTimeFormatter::DAY_OF_WEEK,
        DateTimeFormatter::DAY_OF_WEEK_NAME_SHORT => DateTimeFormatter::DAY_OF_WEEK,

        DateTimeFormatter::DAY_LZ => DateTimeFormatter::DAY,
        DateTimeFormatter::DAY => DateTimeFormatter::DAY,

        DateTimeFormatter::HOURS_LZ => DateTimeFormatter::HOURS,
        DateTimeFormatter::HOURS => DateTimeFormatter::HOURS,
        DateTimeFormatter::HOURS_12_LZ => DateTimeFormatter::HOURS,
        DateTimeFormatter::HOURS_12 => DateTimeFormatter::HOURS,
        DateTimeFormatter::AM_PM_UPPER => DateTimeFormatter::HOURS,
        DateTimeFormatter::AM_PM_LOWER => DateTimeFormatter::HOURS,

        DateTimeFormatter::MINUTES_LZ => DateTimeFormatter::MINUTES,
        DateTimeFormatter::MINUTES => DateTimeFormatter::MINUTES,

        DateTimeFormatter::SECONDS_LZ => DateTimeFormatter::SECONDS,
        DateTimeFormatter::SECONDS => DateTimeFormatter::SECONDS,

        DateTimeFormatter::MILISECONDS_LZ => DateTimeFormatter::MILISECONDS,
        DateTimeFormatter::MILISECONDS => DateTimeFormatter::MILISECONDS,

        DateTimeFormatter::MICROSECONDS_LZ => DateTimeFormatter::MICROSECONDS,
        DateTimeFormatter::MICROSECONDS => DateTimeFormatter::MICROSECONDS,

        DateTimeFormatter::TIMEZONE_NAME => DateTimeFormatter::TIMEZONE_OFFSET,
        DateTimeFormatter::TIMEZONE_NAME_SHORT => DateTimeFormatter::TIMEZONE_OFFSET,
        DateTimeFormatter::TIMEZONE_OFFSET_COLON => DateTimeFormatter::TIMEZONE_OFFSET,
        DateTimeFormatter::TIMEZONE_OFFSET => DateTimeFormatter::TIMEZONE_OFFSET,
        DateTimeFormatter::TIMEZONE_OFFSET_SECONDS => DateTimeFormatter::TIMEZONE_OFFSET,

        DateTimeFormatter::DAYLIGHT_SAVING_TIME => DateTimeFormatter::DAYLIGHT_SAVING_TIME,
    ];
    
    private $cachedFormats;

    /**
     * nodes:
     *     node
     *   | node nodes
     *
     * node:
     *     variable
     *   | group
     *   | constant
     *
     * variable:
     *     /[A-Za-z]/ [modifiers]
     *
     * modifiers:
     *     modifier
     *   | modifier modifiers
     *
     * modifier:
     *     "^"
     *   | "!"
     *   | "="
     *   | ">"
     *   | "<"
     *   | "*"
     *
     * constant:
     *     /[^A-Za-z()[\]{}^!=><*]+/
     *
     * group:
     *     parentheses
     *   | squareBrackets
     *   | curlyBrackets
     *
     * parentheses:
     *     "(" nodes ")"
     *
     * squareBrackets:
     *     "[" nodes "]"
     *
     * curlyBrackets:
     *     "{" nodes "}"
     *
     * @param string $format
     * @return \Dogma\Time\Format\Node
     */
    private function parseFormat(string $format): Node
    {
        //foreach () {
            
        //}
    }

}
