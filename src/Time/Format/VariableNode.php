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
use Dogma\Time\DayOfWeek;
use Dogma\Time\Month;
use Dogma\Time\Seconds;

class VariableNode implements FormatNode
{
    use StrictBehaviorMixin;

    private const GROUPS = [
        SmartDateTimeFormatter::YEAR => 'y',
        SmartDateTimeFormatter::YEAR_SHORT => 'y',
        SmartDateTimeFormatter::LEAP_YEAR => 'l',
        SmartDateTimeFormatter::DAY_OF_YEAR => 'z',
        SmartDateTimeFormatter::DAY_OF_YEAR_INDEX => 'z',
        SmartDateTimeFormatter::QUARTER => 'm',
        SmartDateTimeFormatter::MONTH_LZ => 'm',
        SmartDateTimeFormatter::MONTH => 'm',
        SmartDateTimeFormatter::MONTH_NAME => 'm',
        SmartDateTimeFormatter::MONTH_NAME_SHORT => 'm',
        SmartDateTimeFormatter::DAYS_IN_MONTH => 'k',
        SmartDateTimeFormatter::WEEK_OF_YEAR => 'W',
        SmartDateTimeFormatter::ISO_WEEK_YEAR => 'w',
        SmartDateTimeFormatter::DAY_OF_WEEK => 'b',
        SmartDateTimeFormatter::DAY_OF_WEEK_INDEX => 'b',
        SmartDateTimeFormatter::DAY_OF_WEEK_NAME => 'b',
        SmartDateTimeFormatter::DAY_OF_WEEK_NAME_SHORT => 'b',
        SmartDateTimeFormatter::DAY_LZ => 'd',
        SmartDateTimeFormatter::DAY => 'd',
        SmartDateTimeFormatter::DAY_SUFFIX => 'd',
        SmartDateTimeFormatter::HOURS_LZ => 'h',
        SmartDateTimeFormatter::HOURS => 'h',
        SmartDateTimeFormatter::HOURS_12_LZ => 'h',
        SmartDateTimeFormatter::HOURS_12 => 'h',
        SmartDateTimeFormatter::AM_PM_UPPER => 'h',
        SmartDateTimeFormatter::AM_PM_LOWER => 'h',
        SmartDateTimeFormatter::MINUTES_LZ => 'i',
        SmartDateTimeFormatter::MINUTES => 'i',
        SmartDateTimeFormatter::SECONDS_LZ => 's',
        SmartDateTimeFormatter::SECONDS => 's',
        SmartDateTimeFormatter::MILISECONDS_LZ => 'u',
        SmartDateTimeFormatter::MILISECONDS => 'u',
        SmartDateTimeFormatter::MICROSECONDS_LZ => 'u',
        SmartDateTimeFormatter::MICROSECONDS => 'u',
        SmartDateTimeFormatter::TIMEZONE_NAME => 't',
        SmartDateTimeFormatter::TIMEZONE_NAME_SHORT => 't',
        SmartDateTimeFormatter::TIMEZONE_OFFSET_COLON => 't',
        SmartDateTimeFormatter::TIMEZONE_OFFSET => 'o',
        SmartDateTimeFormatter::TIMEZONE_OFFSET_SECONDS => 'o',
        SmartDateTimeFormatter::DAYLIGHT_SAVING_TIME => 'p',
    ];

    /** @var string */
    public $format;

    /** @var string */
    public $group;

    /** @var string */
    public $modifiers;

    /** @var string */
    public $value;

    /** @var string */
    public $formatted;

    public function __construct(string $variable, string $format, string $modifiers)
    {
        ///
        $foo = $variable;

        $this->format = $format;
        $this->group = self::GROUPS[$format];
        $this->modifiers = $modifiers;
    }

    public function fillValue(DateTimeValues $values): void
    {
        switch ($this->format) {
            case SmartDateTimeFormatter::YEAR:
                $this->value = $values->year;
                $this->formatted = (string) $values->year;
                break;
            case SmartDateTimeFormatter::YEAR_SHORT:
                $this->value = $values->year;
                $this->formatted = substr((string) $values->year, -2);
                break;
            case SmartDateTimeFormatter::LEAP_YEAR:
                $this->value = $values->leapYear;
                $this->formatted = $values->leapYear ? '1' : '0';
                break;
            case SmartDateTimeFormatter::DAY_OF_YEAR:
                $this->value = $values->dayOfYear;
                $this->formatted = (string) ($values->dayOfYear + 1);
                break;
            case SmartDateTimeFormatter::DAY_OF_YEAR_INDEX:
                $this->value = $values->dayOfYear;
                $this->formatted = (string) $values->dayOfYear;
                break;
            case SmartDateTimeFormatter::QUARTER:
                $this->value = $values->quarter;
                $this->formatted = (string) $values->quarter;
                break;
            case SmartDateTimeFormatter::MONTH_LZ:
                $this->value = $values->month;
                $this->formatted = str_pad((string) $values->month, 2, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::MONTH:
                $this->value = $values->month;
                $this->formatted = (string) $values->month;
                break;
            case SmartDateTimeFormatter::MONTH_NAME:
                $this->value = $values->month;
                $this->formatted = Month::get($values->month)->getName();
                break;
            case SmartDateTimeFormatter::MONTH_NAME_SHORT:
                $this->value = $values->month;
                $this->formatted = Month::get($values->month)->getShortcut();
                break;
            case SmartDateTimeFormatter::DAYS_IN_MONTH:
                $this->value = $values->month . '|' . $values->leapYear;
                $this->formatted = (string) Month::get($values->month)->getDays($values->leapYear);
                break;
            case SmartDateTimeFormatter::WEEK_OF_YEAR:
                $this->value = $values->weekOfYear;
                $this->formatted = (string) $values->weekOfYear;
                break;
            case SmartDateTimeFormatter::ISO_WEEK_YEAR:
                $this->value = $values->isoWeekYear;
                $this->formatted = (string) $values->isoWeekYear;
                break;
            case SmartDateTimeFormatter::DAY_OF_WEEK:
                $this->value = $values->dayOfWeek;
                $this->formatted = (string) $values->dayOfWeek;
                break;
            case SmartDateTimeFormatter::DAY_OF_WEEK_INDEX:
                $this->value = $values->dayOfWeek;
                $this->formatted = (string) ($values->dayOfWeek - 1);
                break;
            case SmartDateTimeFormatter::DAY_OF_WEEK_NAME:
                $this->value = $values->dayOfWeek;
                $this->formatted = DayOfWeek::get($values->dayOfWeek)->getName();
                break;
            case SmartDateTimeFormatter::DAY_OF_WEEK_NAME_SHORT:
                $this->value = $values->dayOfWeek;
                $this->formatted = DayOfWeek::get($values->dayOfWeek)->getShortcut();
                break;
            case SmartDateTimeFormatter::DAY_LZ:
                $this->value = $values->day;
                $this->formatted = str_pad((string) $values->day, 2, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::DAY:
                $this->value = $values->day;
                $this->formatted = (string) $values->day;
                break;
            case SmartDateTimeFormatter::DAY_SUFFIX:
                $this->value = $values->day;
                $this->formatted = $values->dateTime->format('S'); ///
                break;
            case SmartDateTimeFormatter::HOURS_LZ:
                $this->value = $values->hours;
                $this->formatted = str_pad((string) $values->hours, 2, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::HOURS:
                $this->value = $values->hours;
                $this->formatted = (string) $values->hours;
                break;
            case SmartDateTimeFormatter::HOURS_12_LZ:
                $this->value = $values->hours;
                $this->formatted = str_pad((string) (($values->hours % 12) ?: 12), 2, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::HOURS_12:
                $this->value = $values->hours;
                $this->formatted = (string) (($values->hours % 12) ?: 12);
                break;
            case SmartDateTimeFormatter::AM_PM_UPPER:
                $this->value = $values->hours;
                $this->formatted = $values->hours >= 12 ? 'PM' : 'AM';
                break;
            case SmartDateTimeFormatter::AM_PM_LOWER:
                $this->value = $values->hours;
                $this->formatted = $values->hours >= 12 ? 'pm' : 'am';
                break;
            case SmartDateTimeFormatter::MINUTES_LZ:
                $this->value = $values->minutes;
                $this->formatted = str_pad((string) $values->minutes, 2, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::MINUTES:
                $this->value = $values->minutes;
                $this->formatted = (string) $values->minutes;
                break;
            case SmartDateTimeFormatter::SECONDS_LZ:
                $this->value = $values->seconds;
                $this->formatted = str_pad((string) $values->seconds, 2, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::SECONDS:
                $this->value = $values->seconds;
                $this->formatted = (string) $values->seconds;
                break;
            case SmartDateTimeFormatter::MILISECONDS_LZ:
                $this->value = $values->microseconds;
                $this->formatted = str_pad((string) floor($values->microseconds / 1000), 3, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::MILISECONDS:
                $this->value = $values->microseconds;
                $this->formatted = (string) floor($values->microseconds / 1000);
                break;
            case SmartDateTimeFormatter::MICROSECONDS_LZ:
                $this->value = $values->microseconds;
                $this->formatted = str_pad((string) $values->microseconds, 6, '0', STR_PAD_LEFT);
                break;
            case SmartDateTimeFormatter::MICROSECONDS:
                $this->value = $values->microseconds;
                $this->formatted = (string) $values->microseconds;
                break;
            case SmartDateTimeFormatter::TIMEZONE_NAME:
                $this->value = $values->timezone->getName();
                $this->formatted = $values->timezone->getName();
                break;
            case SmartDateTimeFormatter::TIMEZONE_NAME_SHORT:
                $this->value = $values->timezone->getName();
                $this->formatted = $values->dateTime->format('T');
                break;
            case SmartDateTimeFormatter::TIMEZONE_OFFSET_COLON:
                $this->value = $values->offset;
                $this->formatted = $values->offset;
                break;
            case SmartDateTimeFormatter::TIMEZONE_OFFSET:
                $this->value = $values->offset;
                $this->formatted = str_replace(':', '', $values->offset);
                break;
            case SmartDateTimeFormatter::TIMEZONE_OFFSET_SECONDS:
                [$hours, $minutes] = explode(':', $values->offset);
                $this->value = $values->offset;
                $this->formatted = $hours * Seconds::HOUR + $minutes * Seconds::MINUTE;
                break;
            case SmartDateTimeFormatter::DAYLIGHT_SAVING_TIME:
                $this->value = $values->dst;
                $this->formatted = $values->dst ? '1' : '0';
                break;
        }
    }

}
