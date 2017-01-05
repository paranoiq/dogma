<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md'; distributed with this source code
 */

namespace Dogma\Language\Locale;

class LocaleNumbers extends \Dogma\Enum
{

    const ARABIC_INDIC = 'arab';
    const ESTENDED_ARABIC_INDIC = 'arabext';
    const ARMENIAN = 'armn';
    const ARMENIAN_LOWERCASE = 'armnlow';
    const BALINESE = 'bali';
    const BENGALI = 'beng';
    const DEVANAGARI = 'deva';
    const ETHIOPIC = 'ethi';
    const FINANCIAL = 'finance';
    const FULL_WIDTH = 'fullwide';
    const GEORGIAN = 'geor';
    const GREEK = 'grek';
    const GREEK_LOWERCASE = 'greklow';
    const GUJARATI = 'gujr';
    const GURMUKHI = 'guru';
    const CHINESE_DECIMAL = 'hanidec';
    const SIMPLIFIES_CHINESE = 'hans';
    const SIMPLIFIES_CHINESE_FINANCIAL = 'hansfin';
    const TRADITIONAL_CHINESE = 'hant';
    const TRADITIONAL_CHINESE_FINANCIAL = 'hantfin';
    const HEBREW = 'hebr';
    const JAVANESE = 'java';
    const JAPANESE = 'jpan';
    const JAPANESE_FINANCIAL = 'jpanfin';
    const KHMER = 'khmr';
    const KANNADA = 'knda';
    const LAO = 'laoo';
    const WESTERN = 'latn';
    const MALAYALAN = 'mlym';
    const MONGOLIAN = 'mong';
    const MYANMAR = 'mymr';
    const NATIVE = 'native';
    const ORIYA = 'orya';
    const OSMANYA = 'osma';
    const ROMAN = 'roman';
    const ROMAN_LOWERCASE = 'romanlow';
    const SAURASHTRA = 'saur';
    const SUNDANESE = 'sund';
    const TRADITIONAL_TAMIL = 'taml';
    const TAMIL = 'tamldec';
    const TELUGU = 'telu';
    const THAI = 'thai';
    const TIBETAN = 'tibt';
    const TRADITIONAL = 'traditional';
    const VAI = 'vaii';

    public static function validateValue(&$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

}
