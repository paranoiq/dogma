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

    public const BIG5 = 'big5han';
    public const DICTIONARY = 'dictionary';
    public const DUCET = 'ducet';
    public const EOR = 'eor';
    public const GB2312 = 'gb2312han';
    public const PHONEBOOK = 'phonebook';
    public const PHONETIC = 'phonetic';
    public const PINYIN = 'pinyin';
    public const REFORMED = 'reformed';
    public const SEARCH = 'search';
    public const SEARCHJL = 'searchjl';
    public const STANDARD = 'standard';
    public const STOKE = 'stroke';
    public const TRADITIONAL = 'traditional';
    public const UNIHAN = 'unihan';
    public const ZHUYIN = 'zhuyin';

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
