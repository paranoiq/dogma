<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

class LocaleCollation extends \Dogma\Enum
{

    const BIG5 = 'big5han';
    const DICTIONARY = 'dictionary';
    const DUCET = 'ducet';
    const EOR = 'eor';
    const GB2312 = 'gb2312han';
    const PHONEBOOK = 'phonebook';
    const PHONETIC = 'phonetic';
    const PINYIN = 'pinyin';
    const REFORMED = 'reformed';
    const SEARCH = 'search';
    const SEARCHJL = 'searchjl';
    const STANDARD = 'standard';
    const STOKE = 'stroke';
    const TRADITIONAL = 'traditional';
    const UNIHAN = 'unihan';
    const ZHUYIN = 'zhuyin';

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
