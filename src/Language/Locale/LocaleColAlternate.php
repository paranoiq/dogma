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

class LocaleColAlternate extends \Dogma\Enum implements \Dogma\Language\Locale\LocaleCollationOption
{

    public const NON_IGNORABLE = 'non-ignorable';
    public const SHIFTED = 'shifted';

    /**
     * @param int|string $value
     * @return bool
     */
    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

    public function getCollatorValue(): int
    {
        return [
            self::NON_IGNORABLE => Collator::NON_IGNORABLE,
            self::SHIFTED => Collator::SHIFTED,
        ][$this->getValue()];
    }

}
