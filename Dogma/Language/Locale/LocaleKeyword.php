<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

class LocaleKeyword extends \Dogma\PartialEnum
{

    const CALENDAR = 'calendar';
    const COLLATION = 'collation';
    const CURRENCY = 'currency';
    const NUMBERS = 'numbers';

    const COL_ALTERNATE = 'colalternate';
    const COL_BACKWARDS = 'colbackwards';
    const COL_CASE_FIRST = 'colcasefirst';
    const COL_HIRAGANA_QUATERNARY = 'colhiraganaquaternary';
    const COL_NORMALIZATION = 'colnormalization';
    const COL_NUMERIC = 'colnumeric';
    const COL_STRENGTH = 'colstrength';

    /**
     * @return string[]
     */
    public static function getCollationOptions(): array
    {
        return [
            self::COL_ALTERNATE => LocaleColAlternate::class,
            self::COL_BACKWARDS => LocaleColBackwards::class,
            self::COL_CASE_FIRST => LocaleColCaseFirst::class,
            self::COL_HIRAGANA_QUATERNARY => LocaleColHiraganaQuaternary::class,
            self::COL_NORMALIZATION => LocaleColNormalization::class,
            self::COL_NUMERIC => LocaleColNumeric::class,
            self::COL_STRENGTH => LocaleColStrength::class,
        ];
    }

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return '[a-z]+';
    }

}
