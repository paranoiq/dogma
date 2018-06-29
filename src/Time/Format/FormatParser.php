<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use Dogma\StrictBehaviorMixin;

class FormatParser
{
    use StrictBehaviorMixin;

    private const DATE_CHARS = [
        SmartDateTimeFormatter::YEAR,
        SmartDateTimeFormatter::YEAR_SHORT,
        SmartDateTimeFormatter::DAY_OF_YEAR,
        SmartDateTimeFormatter::DAY_OF_YEAR_INDEX,
        SmartDateTimeFormatter::LEAP_YEAR,
        SmartDateTimeFormatter::QUARTER,
        SmartDateTimeFormatter::MONTH_LZ,
        SmartDateTimeFormatter::MONTH,
        SmartDateTimeFormatter::MONTH_NAME,
        SmartDateTimeFormatter::MONTH_NAME_SHORT,
        SmartDateTimeFormatter::DAYS_IN_MONTH,
        SmartDateTimeFormatter::WEEK_OF_YEAR,
        SmartDateTimeFormatter::ISO_WEEK_YEAR,
        SmartDateTimeFormatter::DAY_OF_WEEK,
        SmartDateTimeFormatter::DAY_OF_WEEK_INDEX,
        SmartDateTimeFormatter::DAY_OF_WEEK_NAME,
        SmartDateTimeFormatter::DAY_OF_WEEK_NAME_SHORT,
        SmartDateTimeFormatter::DAY_LZ,
        SmartDateTimeFormatter::DAY,
        SmartDateTimeFormatter::DAY_SUFFIX,
    ];

    private const TIME_CHARS = [
        SmartDateTimeFormatter::HOURS_LZ,
        SmartDateTimeFormatter::HOURS,
        SmartDateTimeFormatter::HOURS_12_LZ,
        SmartDateTimeFormatter::HOURS_12,
        SmartDateTimeFormatter::AM_PM_UPPER,
        SmartDateTimeFormatter::AM_PM_LOWER,
        SmartDateTimeFormatter::MINUTES_LZ,
        SmartDateTimeFormatter::MINUTES,
        SmartDateTimeFormatter::SECONDS_LZ,
        SmartDateTimeFormatter::SECONDS,
        SmartDateTimeFormatter::MILISECONDS_LZ,
        SmartDateTimeFormatter::MILISECONDS,
        SmartDateTimeFormatter::MICROSECONDS_LZ,
        SmartDateTimeFormatter::MICROSECONDS,
    ];

    private const TIMEZONE_CHARS = [
        SmartDateTimeFormatter::TIMEZONE_NAME,
        SmartDateTimeFormatter::TIMEZONE_NAME_SHORT,
        SmartDateTimeFormatter::TIMEZONE_OFFSET_COLON,
        SmartDateTimeFormatter::TIMEZONE_OFFSET,
        SmartDateTimeFormatter::TIMEZONE_OFFSET_SECONDS,
        SmartDateTimeFormatter::DAYLIGHT_SAVING_TIME,
    ];

    private const MODIFIER_CHARS = [
        Formatting::UPPER_MODIFIER,
        Formatting::CAPITALIZE_MODIFIER,
        Formatting::WHEN_MODIFIER,
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

    private const SPECIAL_CHARS = self::DATE_CHARS
        + self::TIME_CHARS
        + self::TIMEZONE_CHARS
        + self::MODIFIER_CHARS
        + self::GROUP_START_CHARS
        + self::GROUP_END_CHARS;

    /** @var string[] */
    private static $variables = [
        SmartDateTimeFormatter::YEAR => SmartDateTimeFormatter::YEAR,
        SmartDateTimeFormatter::YEAR_SHORT => SmartDateTimeFormatter::YEAR,
        SmartDateTimeFormatter::LEAP_YEAR => SmartDateTimeFormatter::YEAR,

        SmartDateTimeFormatter::DAY_OF_YEAR => SmartDateTimeFormatter::DAY_OF_YEAR,
        SmartDateTimeFormatter::DAY_OF_YEAR_INDEX => SmartDateTimeFormatter::DAY_OF_YEAR,

        SmartDateTimeFormatter::QUARTER => SmartDateTimeFormatter::QUARTER,

        SmartDateTimeFormatter::MONTH_LZ => SmartDateTimeFormatter::MONTH,
        SmartDateTimeFormatter::MONTH => SmartDateTimeFormatter::MONTH,
        SmartDateTimeFormatter::MONTH_NAME => SmartDateTimeFormatter::MONTH,
        SmartDateTimeFormatter::MONTH_NAME_SHORT => SmartDateTimeFormatter::MONTH,

        SmartDateTimeFormatter::DAYS_IN_MONTH => SmartDateTimeFormatter::DAYS_IN_MONTH,

        SmartDateTimeFormatter::WEEK_OF_YEAR => SmartDateTimeFormatter::WEEK_OF_YEAR,

        SmartDateTimeFormatter::ISO_WEEK_YEAR => SmartDateTimeFormatter::ISO_WEEK_YEAR,

        SmartDateTimeFormatter::DAY_OF_WEEK => SmartDateTimeFormatter::DAY_OF_WEEK,
        SmartDateTimeFormatter::DAY_OF_WEEK_INDEX => SmartDateTimeFormatter::DAY_OF_WEEK,
        SmartDateTimeFormatter::DAY_OF_WEEK_NAME => SmartDateTimeFormatter::DAY_OF_WEEK,
        SmartDateTimeFormatter::DAY_OF_WEEK_NAME_SHORT => SmartDateTimeFormatter::DAY_OF_WEEK,

        SmartDateTimeFormatter::DAY_LZ => SmartDateTimeFormatter::DAY,
        SmartDateTimeFormatter::DAY => SmartDateTimeFormatter::DAY,

        SmartDateTimeFormatter::HOURS_LZ => SmartDateTimeFormatter::HOURS,
        SmartDateTimeFormatter::HOURS => SmartDateTimeFormatter::HOURS,
        SmartDateTimeFormatter::HOURS_12_LZ => SmartDateTimeFormatter::HOURS,
        SmartDateTimeFormatter::HOURS_12 => SmartDateTimeFormatter::HOURS,
        SmartDateTimeFormatter::AM_PM_UPPER => SmartDateTimeFormatter::HOURS,
        SmartDateTimeFormatter::AM_PM_LOWER => SmartDateTimeFormatter::HOURS,

        SmartDateTimeFormatter::MINUTES_LZ => SmartDateTimeFormatter::MINUTES,
        SmartDateTimeFormatter::MINUTES => SmartDateTimeFormatter::MINUTES,

        SmartDateTimeFormatter::SECONDS_LZ => SmartDateTimeFormatter::SECONDS,
        SmartDateTimeFormatter::SECONDS => SmartDateTimeFormatter::SECONDS,

        SmartDateTimeFormatter::MILISECONDS_LZ => SmartDateTimeFormatter::MILISECONDS,
        SmartDateTimeFormatter::MILISECONDS => SmartDateTimeFormatter::MILISECONDS,

        SmartDateTimeFormatter::MICROSECONDS_LZ => SmartDateTimeFormatter::MICROSECONDS,
        SmartDateTimeFormatter::MICROSECONDS => SmartDateTimeFormatter::MICROSECONDS,

        SmartDateTimeFormatter::TIMEZONE_NAME => SmartDateTimeFormatter::TIMEZONE_OFFSET,
        SmartDateTimeFormatter::TIMEZONE_NAME_SHORT => SmartDateTimeFormatter::TIMEZONE_OFFSET,
        SmartDateTimeFormatter::TIMEZONE_OFFSET_COLON => SmartDateTimeFormatter::TIMEZONE_OFFSET,
        SmartDateTimeFormatter::TIMEZONE_OFFSET => SmartDateTimeFormatter::TIMEZONE_OFFSET,
        SmartDateTimeFormatter::TIMEZONE_OFFSET_SECONDS => SmartDateTimeFormatter::TIMEZONE_OFFSET,

        SmartDateTimeFormatter::DAYLIGHT_SAVING_TIME => SmartDateTimeFormatter::DAYLIGHT_SAVING_TIME,
    ];

    /** @var \Dogma\Time\Format\BranchNode[] */
    private $cachedFormats;

    /**
     * Formal grammar:
     *
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
     *   | "\" /[A-Za-z()[\]{}^!=><*]+/
     *   | "%" /[A-Za-z()[\]{}^!=><*]+/
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

    /**
     * @param string[] $chars
     * @param int $position
     * @return string[]
     */
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
                    ///
                    break;
                case Formatting::NO_ZEROS_GROUP_END:
                case Formatting::OPTIONAL_GROUP_END:
                case Formatting::NO_DUPLICATION_GROUP_END:
                    ///
                    break;
                case Formatting::UPPER_MODIFIER:
                case Formatting::CAPITALIZE_MODIFIER:
                case Formatting::WHEN_MODIFIER:
                case Formatting::SINCE_MODIFIER:
                case Formatting::UNTIL_MODIFIER:
                case Formatting::ORDINAL_MODIFIER:
                    $modifiers[] = $char;
                    break;
                case SmartDateTimeFormatter::YEAR:
                case SmartDateTimeFormatter::YEAR_SHORT:
                case SmartDateTimeFormatter::DAY_OF_YEAR:
                case SmartDateTimeFormatter::DAY_OF_YEAR_INDEX:
                case SmartDateTimeFormatter::LEAP_YEAR:
                case SmartDateTimeFormatter::QUARTER:
                case SmartDateTimeFormatter::MONTH_LZ:
                case SmartDateTimeFormatter::MONTH:
                case SmartDateTimeFormatter::MONTH_NAME:
                case SmartDateTimeFormatter::MONTH_NAME_SHORT:
                case SmartDateTimeFormatter::DAYS_IN_MONTH:
                case SmartDateTimeFormatter::WEEK_OF_YEAR:
                case SmartDateTimeFormatter::ISO_WEEK_YEAR:
                case SmartDateTimeFormatter::DAY_OF_WEEK:
                case SmartDateTimeFormatter::DAY_OF_WEEK_INDEX:
                case SmartDateTimeFormatter::DAY_OF_WEEK_NAME:
                case SmartDateTimeFormatter::DAY_OF_WEEK_NAME_SHORT:
                case SmartDateTimeFormatter::DAY_LZ:
                case SmartDateTimeFormatter::DAY:
                case SmartDateTimeFormatter::DAY_SUFFIX:
                    ///
                    break;
            }
        }

        return $nodes;
    }

}
