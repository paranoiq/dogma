<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: UO dow ofs tz µs pátek od pátku dubna duben

namespace Dogma\Time\Format;

use Dogma\Language\Localization\Translator;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DayOfWeek;
use Dogma\Time\Month;
use Dogma\Time\Provider\CurrentTimeProvider;
use Dogma\Time\Provider\TimeProvider;

class SmartDateTimeFormatter
{
    use StrictBehaviorMixin;

    public const YEAR = 'Y'; // Y
    public const YEAR_SHORT = 'y'; // y
    public const DAY_OF_YEAR = 'Z'; // --
    public const DAY_OF_YEAR_INDEX = 'z'; // z
    public const LEAP_YEAR = 'r'; // L
    public const QUARTER = 'Q'; // --

    public const MONTH_LZ = 'M'; // m
    public const MONTH = 'm'; // n
    public const MONTH_NAME = 'N'; // F
    public const MONTH_NAME_SHORT = 'n'; // M
    public const DAYS_IN_MONTH = 'k'; // t

    public const WEEK_OF_YEAR = 'W'; // W
    public const ISO_WEEK_YEAR = 'w'; // o
    public const DAY_OF_WEEK = 'B'; // N
    public const DAY_OF_WEEK_INDEX = 'b'; // w
    public const DAY_OF_WEEK_NAME = 'C'; // l
    public const DAY_OF_WEEK_NAME_SHORT = 'c';

    public const DAY_LZ = 'D'; // d
    public const DAY = 'd'; // j
    public const DAY_SUFFIX = 'e'; // S

    public const HOURS_LZ = 'H'; // H
    public const HOURS = 'h'; // G
    public const HOURS_12_LZ = 'G'; // h
    public const HOURS_12 = 'g'; // g
    public const AM_PM_UPPER = 'A'; // A
    public const AM_PM_LOWER = 'a'; // a

    public const MINUTES_LZ = 'I'; // i
    public const MINUTES = 'i'; // --
    public const SECONDS_LZ = 'S'; // s
    public const SECONDS = 's'; // --
    public const MILISECONDS_LZ = 'V'; // v
    public const MILISECONDS = 'v'; // --
    public const MICROSECONDS_LZ = 'U'; // u
    public const MICROSECONDS = 'u'; // --

    public const TIMEZONE_NAME = 'T'; // e
    public const TIMEZONE_NAME_SHORT = 't'; // T
    public const TIMEZONE_OFFSET_COLON = 'O'; // P
    public const TIMEZONE_OFFSET = 'o'; // P
    public const TIMEZONE_OFFSET_SECONDS = 'P'; // Z
    public const DAYLIGHT_SAVING_TIME = 'p'; // I

    public const FORMAT_DEFAULT = 'Y-M-D H:I:S';
    public const FORMAT_ISO_TZ = 'Y-M-D%TH:I:SO';
    public const FORMAT_ISO_MICRO_TZ = 'Y-M-D%TH:I:S.UO';

    /** @var \Dogma\Language\Localization\Translator|null */
    private $translator;

    /** @var \Dogma\Time\Provider\TimeProvider */
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
     * %    %   Escape character. Use %% for printing "%"
     *
     * Skip groups:
     * [    -   Group start, skip if zero
     * ]    -   Group end, skip if zero
     * (    -   Group start, skip if same as now
     * )    -   Group end, skip if same as now
     *
     * Modifiers:
     * ^    -   Upper case letters "FRIDAY"
     * !    -   Starts with upper case letter "Friday"
     * =    -   "At" case, eg. "C=" --> "at friday", "v pátek"
     * >    -   "Since" case, eg. "C>" --> "since friday", "od pátku"
     * <    -   "Until" case, eg. "C<" --> "until friday", "do pátku"
     * *    -   "Of" case for names and ordinal suffix for numbers
     *              - eg. "d* N*" --> "27th of april", "27. dubna"
     *              - eg. "N d*" --> "april 27th", "duben 27."
     *
     * Objects:
     * Y    Y   Year, 4 digits                      dt, d
     * y    y   Year, 2 digits                      dt, d
     * Z    -   Day of the year (1-366)             dt, d
     * z    z   Day of the year (0-365)             dt, d
     * Q    -   Quarter (1-4)                       dt, d
     * r    L   Leap year (0/1)                     dt, d
     *
     * M    m   Month number with leading zeros     dt, d
     * m    n   Month number                        dt, d
     * N    F   Month name                          dt, d
     * n    M   Month name short                    dt, d
     * k    t   Number of days in month             dt, d
     *
     * W    W   Week of year (ISO)                  dt, d
     * w    o   Year of week (ISO)                  dt, d
     * B    N   Day of week number (ISO, 1-7)       dt, d
     * b    w   Day of week index (0-6)             dt, d
     * C    l   Day of week name                    dt, d
     * c    D   Day of week name short              dt, d
     *
     * D    d   Day of month with leading zeros     dt, d
     * d    j   Day of month                        dt, d
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
     * V    v   Miliseconds with leading zeros      dt, t
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
                return $dateTime->format($nativeFormat);
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
        $from = '{}[]()ZQrMmNnLkwBbCcFDdefGIiSsVvUuTtOoPp^!=<>*';
        $to   = '^^^^^^^^LmnfM^toNwlD^djSGhi^s^^^u^eTPOZI^^^^^^';

        $format = strtr($format, $from, strtr($to, '^', "\0"));
        if (strstr($format, "\0") === false) {
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
        return Month::getNames();
    }

    /**
     * @return string[]
     */
    protected function getDaysOfWeek(): array
    {
        return DayOfWeek::getNames();
    }

}
