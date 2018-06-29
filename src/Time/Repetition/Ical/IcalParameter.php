<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: TZID

namespace Dogma\Time\Repetition\Ical;

use Dogma\Enum\StringEnum;
use Dogma\Language\Locale\Locale;

class IcalParameter extends StringEnum
{

    public const LANGUAGE = 'LANGUAGE';
    public const TIME_ZONE_ID = 'TZID';
    public const VALUE = 'VALUE';

    public $types = [
        self::LANGUAGE => Locale::class,
        self::TIME_ZONE_ID => \DateTimeZone::class,
        self::VALUE => IcalValueType::class,
    ];

}
