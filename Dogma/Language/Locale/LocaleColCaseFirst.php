<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

use Dogma\Language\Collator;

class LocaleColCaseFirst extends \Dogma\Enum implements \Dogma\Language\Locale\LocaleCollationOption
{

    const UPPER = 'upper';
    const LOWER = 'lower';
    const NO = 'no';

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

    public function getCollatorValue(): int
    {
        return [
            self::UPPER => Collator::UPPER_FIRST,
            self::LOWER => Collator::LOWER_FIRST,
            self::NO => Collator::OFF,
        ][$this->getValue()];
    }

}