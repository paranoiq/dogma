<?php declare(strict_types = 1);

namespace Dogma\Time;

use Dogma\Enum\StringEnum;

class DateTimeUnit extends StringEnum
{

    public const YEAR = 'year';
    public const QUARTER = 'quarter';
    public const MONTH = 'month';
    public const WEEK = 'week';
    public const DAY = 'day';

    public const HOUR = 'hour';
    public const MINUTE = 'minute';
    public const SECOND = 'second';
    public const MILISECOND = 'milisecond';
    public const MICROSECOND = 'microsecond';

    /**
     * @return string[]
     */
    public static function getDateUnits(): array
    {
        return [
            self::YEAR,
            self::MONTH,
            self::WEEK,
            self::DAY,
        ];
    }

    /**
     * @return string[]
     */
    public static function getTimeUnits(): array
    {
        return [
            self::HOUR,
            self::MINUTE,
            self::SECOND,
            self::MILISECOND,
            self::MICROSECOND,
        ];
    }

    /**
     * @return string[]
     */
    public static function getComparisonFormats(): array
    {
        return [
            self::YEAR => 'Y',
            self::MONTH => 'Ym',
            self::WEEK => 'oW',
            self::DAY => 'Ymd',
            self::HOUR => 'YmdH',
            self::MINUTE => 'YmdHi',
            self::SECOND => 'YmdHis',
            self::MILISECOND => 'YmdHisv',
            self::MICROSECOND => 'YmdHisu',
        ];
    }

    public function isDate(): bool
    {
        return in_array($this->getValue(), self::getDateUnits());
    }

    public function isTime(): bool
    {
        return in_array($this->getValue(), self::getTimeUnits());
    }

    /**
     * Used in DateTime::equalsUpTo()
     * @return string
     */
    public function getComparisonFormat(): string
    {
        return self::getComparisonFormat()[$this->getValue()];
    }

}
