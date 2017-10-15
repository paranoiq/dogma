<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

use Dogma\Localisation\Translator;

class DateTimeFormatter
{
    use \Dogma\StrictBehaviorMixin;

    public const YEAR = 'Y';
    public const YEAR_SHORT = 'y';
    public const DAY_OF_YEAR = 'Z';
    public const DAY_OF_YEAR_INDEX = 'z';
    public const LEAP_YEAR = 'r';
    public const QUARTER = 'Q';

    public const MONTH_LZ = 'M';
    public const MONTH = 'm';
    public const MONTH_NAME = 'N';
    public const MONTH_NAME_SHORT = 'n';
    public const MONTH_NAME_LOKATIV = 'L';
    public const DAYS_IN_MONTH = 'k';

    public const WEEK_OF_YEAR = 'W';
    public const ISO_WEEK_YEAR = 'w';
    public const DAY_OF_WEEK = 'B';
    public const DAY_OF_WEEK_INDEX = 'b';
    public const DAY_OF_WEEK_NAME = 'C';
    public const DAY_OF_WEEK_NAME_SHORT = 'c';
    public const DAY_OF_WEEK_LOKATIV = 'F';

    public const DAY_LZ = 'D';
    public const DAY = 'd';
    public const DAY_SUFFIX = 'e';

    public const HOURS_LZ = 'H';
    public const HOURS = 'h';
    public const HOURS_12_LZ = 'G';
    public const HOURS_12 = 'g';
    public const AM_PM_UPPER = 'A';
    public const AM_PM_LOWER = 'a';

    public const MINUTES_LZ = 'I';
    public const MINUTES = 'i';
    public const SECONDS_LZ = 'S';
    public const SECONDS = 's';
    public const MILISECONDS_LZ = 'V';
    public const MILISECONDS = 'v';
    public const MICROSECONDS_LZ = 'U';
    public const MICROSECONDS = 'u';

    public const TIMEZONE_NAME = 'T';
    public const TIMEZONE_NAME_SHORT = 't';
    public const TIMEZONE_OFFSET_COLON = 'O';
    public const TIMEZONE_OFFSET = 'o';
    public const TIMEZONE_OFFSET_SECONDS = 'P';
    public const DAYLIGHT_SAVING_TIME = 'p';

    public const FORMAT_DEFAULT = 'Y-M-D H:I:S';
    public const FORMAT_ISO_TZ = 'Y-M-D%TH:I:SO';
    public const FORMAT_ISO_MICRO_TZ = 'Y-M-D%TH:I:S.UO';

    private static $specialCharacters = [
        self::YEAR,
        self::YEAR_SHORT,
        self::DAY_OF_YEAR,
        self::DAY_OF_YEAR_INDEX,
        self::LEAP_YEAR,
        self::QUARTER,
        self::MONTH_LZ,
        self::MONTH,
        self::MONTH_NAME,
        self::MONTH_NAME_SHORT,
        self::DAYS_IN_MONTH,
        self::WEEK_OF_YEAR,
        self::ISO_WEEK_YEAR,
        self::DAY_OF_WEEK,
        self::DAY_OF_WEEK_INDEX,
        self::DAY_OF_WEEK_NAME,
        self::DAY_OF_WEEK_NAME_SHORT,
        self::DAY_LZ,
        self::DAY,
        self::DAY_SUFFIX,
        self::HOURS_LZ,
        self::HOURS,
        self::HOURS_12_LZ,
        self::HOURS_12,
        self::AM_PM_UPPER,
        self::AM_PM_LOWER,
        self::MINUTES_LZ,
        self::MINUTES,
        self::SECONDS_LZ,
        self::SECONDS,
        self::MILISECONDS_LZ,
        self::MILISECONDS,
        self::MICROSECONDS_LZ,
        self::MICROSECONDS,
        self::TIMEZONE_NAME,
        self::TIMEZONE_NAME_SHORT,
        self::TIMEZONE_OFFSET_COLON,
        self::TIMEZONE_OFFSET,
        self::TIMEZONE_OFFSET_SECONDS,
        self::DAYLIGHT_SAVING_TIME,
    ];

    private static $dateCharacters = [
        self::YEAR,
        self::YEAR_SHORT,
        self::DAY_OF_YEAR,
        self::DAY_OF_YEAR_INDEX,
        self::LEAP_YEAR,
        self::QUARTER,
        self::MONTH_LZ,
        self::MONTH,
        self::MONTH_NAME,
        self::MONTH_NAME_SHORT,
        self::DAYS_IN_MONTH,
        self::WEEK_OF_YEAR,
        self::ISO_WEEK_YEAR,
        self::DAY_OF_WEEK,
        self::DAY_OF_WEEK_INDEX,
        self::DAY_OF_WEEK_NAME,
        self::DAY_OF_WEEK_NAME_SHORT,
        self::DAY_LZ,
        self::DAY,
        self::DAY_SUFFIX,
    ];

    private static $timeCharacters = [
        self::HOURS_LZ,
        self::HOURS,
        self::HOURS_12_LZ,
        self::HOURS_12,
        self::AM_PM_UPPER,
        self::AM_PM_LOWER,
        self::MINUTES_LZ,
        self::MINUTES,
        self::SECONDS_LZ,
        self::SECONDS,
        self::MILISECONDS_LZ,
        self::MILISECONDS,
        self::MICROSECONDS_LZ,
        self::MICROSECONDS,
    ];

    private static $timezoneCharacters = [
        self::TIMEZONE_NAME,
        self::TIMEZONE_NAME_SHORT,
        self::TIMEZONE_OFFSET_COLON,
        self::TIMEZONE_OFFSET,
        self::TIMEZONE_OFFSET_SECONDS,
        self::DAYLIGHT_SAVING_TIME,
    ];

    private const MONTH_LENGTHS = [
        1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31
    ];

    /** @var \Dogma\Localisation\Translator|null */
    private $translator;

    /** @var \Dogma\Time\TimeProvider */
    private $timeProvider;

    public function __construct(?Translator $translator = null, ?TimeProvider $timeProvider = null)
    {
        $this->translator = $translator;
        $this->timeProvider = $timeProvider ?? new CurrentTimeProvider();
    }

    /**
     * Letter assignment:
     * am dow day - - hour min - - L month ofs q - s tz µs ms w year_
     * A  B C D   E F G H  I   J K L M N   O P Q R S T  U  V  W X Y Z
     *
     * Use "[" and "]" for optional groups. Group containing a value of zero will not be part of output. Eg:
     * - "[m O, ]d F" will print "5 months, 10 days" or "10 days" if months are 0.
     * - "[m O, d F, ]h l" will print "0 months, 5 days, 2 hours" because both values inside group must be 0 to skip group.
     *
     * Ch.  Nt. Description                         Applicable to
     * ---- --- ----------------------------------- -------------
     * Escaping:
     * %	%   Escape character. Use %% for printing "%"
     *
     * Skip groups:
     * [    -   Group start, skip if zero
     * ]    -   Group end, skip if zero
     * (    -   Group start, skip if same as today
     * )    -   Group end, skip if same as today
     *
     * Modifiers:
     * ^    -   Upper case letters
     * !    -   Starts with upper case letter
     * =    -   "At" case, eg. "C=" --> "at friday", "v pátek"
     * >    -   "Since" case, eg. "C<" --> "until friday", "od pátku"
     * <    -   "Until" case, eg. "C>" --> "since friday", "do pátku"
     * *    -   "Of" case for names and ordinal suffix for numbers
     *              - eg. "d* N*" --> "27th of april", "27. dubna"
     *              - eg. "N d*" --> "april 27th", "duben 27."
     *
     * Objects:
     * Y    Y   Year, 4 digits                      d, td
     * y    y   Year, 2 digits                      d, dt
     * Z    -   Day of the year (1-366)             d, dt
     * z    z   Day of the year (0-365)             d, dt
     * Q    -   Quarter (1-4)                       d, dt
     * r    L   Leap year (0/1)                     d, dt
     *
     * M    m   Month number with leading zeros     d, dt
     * m    n   Month number                        d, dt
     * N    F   Month name                          d, dt
     * n    M   Month name short                    d, dt
     * k    t   Number of days in month             d, dt
     *
     * W    W   Week of year (ISO)                  d, dt
     * w    o   Year of week (ISO)                  d, dt
     * B    N   Day of week number (ISO, 1-7)       d, dt
     * b    w   Day of week index (0-6)             d, dt
     * C    l   Day of week name                    d, dt
     * c    D   Day of week name short              d, dt
     *
     * D    d   Day of month with leading zeros     d, dt
     * d    j   Day of month                        d, dt
     *
     * H    H   Hours (24) with leading zeros       dt, t
     * h    G   Hours (24)                          dt, t
     * G    h   Hours (12) with leading zeros       dt, t
     * g    g   Hours (12)                          dt, t
     * A    A   AM/PM                               dt, t
     * a    a   am/pm                               dt, t
     *
     * I    i   Minutes with leading zeros          dt, t
     * i    -   Minutes                             dt, t
     * S    s   Seconds with leading zeros          dt, t
     * s    -   Seconds                             dt, t
     * V    -   Miliseconds with leading zeros      dt, t
     * v    -   Miliseconds                         dt, t
     * U    u   Microseconds with leading zeros     dt, t
     * u    -   Microseconds                        dt, t
     *
     * T    e   Timezone name                       dt
     * t    T   Timezone abbreviation               dt
     * O    P   Timezone offset with ":" ("01:00")  dt
     * o    O   Timezone offset ("0000")            dt
     * P    Z   Timezone offset in seconds          dt
     * p    I   Daylight saving time (0/1)          dt
     *
     * @param \DateTimeInterface|\Dogma\Time\Date|\Dogma\Time\Time $dateTime
     * @param string|null $format
     * @return string
     */
    public function format($dateTime, ?string $format = null): string
    {
        if ($this->translator === null) {
            $nativeFormat = $this->getNativeFormat($format);
            if ($nativeFormat !== null) {
                // faster
                return $dateTime->format();
            }
        }

        ///
    }

    /**
     * @param string $format
     * @return string|null
     */
    private function getNativeFormat(string $format): ?string
    {
        static $map = [
            '[' => "\0",
            ']' => "\0",
            '(' => "\0",
            ')' => "\0",
            'Z' => "\0",
            'Q' => "\0",
            'r' => 'L',
            'M' => 'm',
            'm' => 'n',
            'N' => 'f',
            'n' => 'M',
            'L' => "\0",
            'k' => 't',
            'w' => 'o',
            'B' => 'N',
            'b' => 'w',
            'C' => 'l',
            'c' => 'D',
            'F' => "\0",
            'D' => 'd',
            'd' => 'j',
            'e' => 'S',
            'h' => 'G',
            'G' => 'h',
            'I' => 'i',
            'i' => "\0",
            'S' => 's',
            's' => "\0",
            'V' => "\0",
            'v' => "\0",
            'U' => 'u',
            'u' => "\0",
            'T' => 'e',
            't' => 'T',
            'O' => 'P',
            'o' => 'O',
            'P' => 'Z',
            'p' => 'I',
        ];

        $format = str_replace(array_keys($map), array_values($map), $format);
        if (strstr($format, "\0") !== false) {
            return $format;
        } else {
            return null;
        }
    }

    private function isLeapYear(int $year): bool
    {
        return ($year % 4) && (!($year % 100) || ($year % 400));
    }

    /**
     * @return string[]
     */
    protected function getMonths(): array
    {
        return Month::getEnglishNames();
    }

    /**
     * @return string[]
     */
    protected function getDaysOfWeek(): array
    {
        return DayOfWeek::getEnglishNames();
    }

}