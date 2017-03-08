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

    public const BUDDHIST = 'buddhist';
    public const CHINESE = 'chinese';
    public const COPTIC = 'coptic';
    public const DANGI = 'dangi';
    public const ETHIOPIC = 'ethiopic';
    public const ETHIOPIC_AMETE_ALEM = 'ethiopic-amete-alem';
    public const GREGORIAN = 'gregorian';
    public const HEBREW = 'hebrew';
    public const INDIAN = 'indian';
    public const ISLAMIC = 'islamic';
    public const ISLAMIC_CIVIL = 'islamic-civil';
    public const ISO8601 = 'iso8601';
    public const JAPANESE = 'japanese';
    public const PERSIAN = 'persian';
    public const ROC = 'roc';

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
