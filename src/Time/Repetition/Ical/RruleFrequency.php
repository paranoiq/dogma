<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition\Ical;

use Dogma\Enum\StringEnum;
use Dogma\LogicException;
use Dogma\Time\DateTimeUnit;

class RruleFrequency extends StringEnum
{

    public const YEARLY = 'YEARLY';
    public const MONTHLY = 'MONTHLY';
    public const WEEKLY = 'WEEKLY';
    public const DAILY = 'DAILY';
    public const HOURLY = 'HOURLY';
    public const MINUTELY = 'MINUTELY';
    public const SECONDLY = 'SECONDLY';

    public static function fromTimeUnit(DateTimeUnit $unit): self
    {
        switch ($unit->getValue()) {
            case DateTimeUnit::YEAR:
                return self::get(self::YEARLY);
            case DateTimeUnit::MONTH:
                return self::get(self::MONTHLY);
            case DateTimeUnit::WEEK:
                return self::get(self::WEEKLY);
            case DateTimeUnit::DAY:
                return self::get(self::DAILY);
            case DateTimeUnit::HOUR:
                return self::get(self::HOURLY);
            case DateTimeUnit::MINUTE:
                return self::get(self::MINUTELY);
            case DateTimeUnit::SECOND:
                return self::get(self::SECONDLY);
            default:
                throw new LogicException(sprintf('%s is not supported by iCal repetition rules', ucfirst($unit->getValue())));
        }
    }

}
