<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: FMTTYPE vnd microsoft TZID BYMONTH BYDAY BYHOUR BYMINUTE

namespace Dogma\Time\Repetition\Ical;

use Dogma\Enum\StringEnum;

class IcalValueType extends StringEnum
{

    public const BINARY = 'BINARY'; // FMTTYPE=image/vnd.microsoft.icon;ENCODING=BASE64;VALUE=BINARY:...
    public const BOOLEAN = 'BOOLEAN'; // TRUE / FALSE
    public const CAL_ADDRESS = 'CAL-ADDRESS'; // jane_doe@example.com
    public const DATE = 'DATE'; // 19970714
    public const DATETIME = 'DATE-TIME'; // 19980118T230000 / 19980119T070000Z / TZID=America/New_York:19980119T020000
    public const DURATION = 'DURATION'; // P15DT5H0M20S
    public const FLOAT = 'FLOAT'; // -3.14
    public const INTEGER = 'INTEGER'; // 432109876
    public const PERIOD = 'PERIOD'; // 19970101T180000Z/19970102T070000Z / 19970101T180000Z/PT5H30M
    public const RECUR = 'RECUR'; // FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30
    public const TEXT = 'TEXT'; // Foo
    public const TIME = 'TIME'; // 083000 / 133000Z / TZID=America/New_York:083000
    public const URI = 'URI'; // http://example.com/
    public const UTC_OFFSET = 'UTC-OFFSET'; // +0100

}
