<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

class LocaleCalendar extends \Dogma\Enum
{

    const BUDDHIST = 'buddhist';
    const CHINESE = 'chinese';
    const COPTIC = 'coptic';
    const DANGI = 'dangi';
    const ETHIOPIC = 'ethiopic';
    const ETHIOPIC_AMETE_ALEM = 'ethiopic-amete-alem';
    const GREGORIAN = 'gregorian';
    const HEBREW = 'hebrew';
    const INDIAN = 'indian';
    const ISLAMIC = 'islamic';
    const ISLAMIC_CIVIL = 'islamic-civil';
    const ISO8601 = 'iso8601';
    const JAPANESE = 'japanese';
    const PERSIAN = 'persian';
    const ROC = 'roc';

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
