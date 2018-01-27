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

    private const DATE_CHARS = [
        DateTimeFormatter::YEAR,
        DateTimeFormatter::YEAR_SHORT,
        DateTimeFormatter::DAY_OF_YEAR,
        DateTimeFormatter::DAY_OF_YEAR_INDEX,
        DateTimeFormatter::LEAP_YEAR,
        DateTimeFormatter::QUARTER,
        DateTimeFormatter::MONTH_LZ,
        DateTimeFormatter::MONTH,
        DateTimeFormatter::MONTH_NAME,
        DateTimeFormatter::MONTH_NAME_SHORT,
        DateTimeFormatter::DAYS_IN_MONTH,
        DateTimeFormatter::WEEK_OF_YEAR,
        DateTimeFormatter::ISO_WEEK_YEAR,
        DateTimeFormatter::DAY_OF_WEEK,
        DateTimeFormatter::DAY_OF_WEEK_INDEX,
        DateTimeFormatter::DAY_OF_WEEK_NAME,
        DateTimeFormatter::DAY_OF_WEEK_NAME_SHORT,
        DateTimeFormatter::DAY_LZ,
        DateTimeFormatter::DAY,
        DateTimeFormatter::DAY_SUFFIX,
    ];

    private const TIME_CHARS = [
        DateTimeFormatter::HOURS_LZ,
        DateTimeFormatter::HOURS,
        DateTimeFormatter::HOURS_12_LZ,
        DateTimeFormatter::HOURS_12,
        DateTimeFormatter::AM_PM_UPPER,
        DateTimeFormatter::AM_PM_LOWER,
        DateTimeFormatter::MINUTES_LZ,
        DateTimeFormatter::MINUTES,
        DateTimeFormatter::SECONDS_LZ,
        DateTimeFormatter::SECONDS,
        DateTimeFormatter::MILISECONDS_LZ,
        DateTimeFormatter::MILISECONDS,
        DateTimeFormatter::MICROSECONDS_LZ,
        DateTimeFormatter::MICROSECONDS,
    ];

    private const TIMEZONE_CHARS = [
        DateTimeFormatter::TIMEZONE_NAME,
        DateTimeFormatter::TIMEZONE_NAME_SHORT,
        DateTimeFormatter::TIMEZONE_OFFSET_COLON,
        DateTimeFormatter::TIMEZONE_OFFSET,
        DateTimeFormatter::TIMEZONE_OFFSET_SECONDS,
        DateTimeFormatter::DAYLIGHT_SAVING_TIME,
    ];

    private const MODIFIER_CHARS = [
        Formatting::UPPER_CASE_MODIFIER,
        Formatting::FIRST_UPPER_MODIFIER,
        Formatting::IN_ON_AT_MODIFIER,
        Formatting::SINCE_MODIFIER,
        Formatting::UNTIL_MODIFIER,
        Formatting::ORDINAL_MODIFIER,
    ];

    private const GROUP_START_CHARS = [
        Formatting::NO_ZEROS_GROUP_START,
        Formatting::OPTIONAL_GROUP_START,
        Formatting::NO_DUPLICATION_GROUP_START,
    ];

    private const GROUP_END_CHARS = [
        Formatting::NO_ZEROS_GROUP_END,
        Formatting::OPTIONAL_GROUP_END,
        Formatting::NO_DUPLICATION_GROUP_END,
    ];

    private const SPECIAL_CHARS = self::DATE_CHARS + self::TIME_CHARS + self::TIMEZONE_CHARS + self::MODIFIER_CHARS + self::GROUP_START_CHARS + self::GROUP_END_CHARS;
    
    private static $variables = [
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

    /** @var \Dogma\Time\Format\BranchNode[] */
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
     * @return \Dogma\Time\Format\BranchNode
     */
    public function parseFormat(string $format): BranchNode
    {
        if (isset($this->cachedFormats[$format])) {
            return $this->cachedFormats[$format];
        }
        $chars = explode('', $format);
        $nodes = $this->parseList($chars, 0);

        return new BranchNode(BranchNode::LIST, $nodes);
    }

    private function parseList(array $chars, int $position): array
    {
        $nodes = [];
        $modifiers = [];

        $escaped = false;
        while (isset($chars[$position])) {
            $char = $chars[$position];
            if ($escaped) {
                $nodes[] = $char;
                $escaped = false;
                continue;
            }
            switch ($char) {
                case Formatting::ESCAPE_CHARACTER:
                    $escaped = true;
                    continue;
                case Formatting::NO_ZEROS_GROUP_START:
                case Formatting::OPTIONAL_GROUP_START:
                case Formatting::NO_DUPLICATION_GROUP_START:

                    break;
                case Formatting::NO_ZEROS_GROUP_END:
                case Formatting::OPTIONAL_GROUP_END:
                case Formatting::NO_DUPLICATION_GROUP_END:

                    break;
                case Formatting::UPPER_CASE_MODIFIER:
                case Formatting::FIRST_UPPER_MODIFIER:
                case Formatting::IN_ON_AT_MODIFIER:
                case Formatting::SINCE_MODIFIER:
                case Formatting::UNTIL_MODIFIER:
                case Formatting::ORDINAL_MODIFIER:
                    $modifiers[] = $char;
                    break;
                case DateTimeFormatter::YEAR:
                case DateTimeFormatter::YEAR_SHORT:
                case DateTimeFormatter::DAY_OF_YEAR:
                case DateTimeFormatter::DAY_OF_YEAR_INDEX:
                case DateTimeFormatter::LEAP_YEAR:
                case DateTimeFormatter::QUARTER:
                case DateTimeFormatter::MONTH_LZ:
                case DateTimeFormatter::MONTH:
                case DateTimeFormatter::MONTH_NAME:
                case DateTimeFormatter::MONTH_NAME_SHORT:
                case DateTimeFormatter::DAYS_IN_MONTH:
                case DateTimeFormatter::WEEK_OF_YEAR:
                case DateTimeFormatter::ISO_WEEK_YEAR:
                case DateTimeFormatter::DAY_OF_WEEK:
                case DateTimeFormatter::DAY_OF_WEEK_INDEX:
                case DateTimeFormatter::DAY_OF_WEEK_NAME:
                case DateTimeFormatter::DAY_OF_WEEK_NAME_SHORT:
                case DateTimeFormatter::DAY_LZ:
                case DateTimeFormatter::DAY:
                case DateTimeFormatter::DAY_SUFFIX:
                    ///
                    break;
            }
        }

        return $nodes;
    }

}
