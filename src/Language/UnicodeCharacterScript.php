<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

use Dogma\Enum\StringEnum;

class UnicodeCharacterScript extends StringEnum
{

    public const ARABIC = 'Arabic';
    public const ARMENIAN = 'Armenian';
    public const AVESTAN = 'Avestan';
    public const BALINESE = 'Balinese';
    public const BAMUM = 'Bamum';
    public const BATAK = 'Batak';
    public const BENGALI = 'Bengali';
    public const BOPOMOFO = 'Bopomofo';
    public const BRAHMI = 'Brahmi';
    public const BRAILLE = 'Braille';
    public const BUGINESE = 'Buginese';
    public const BUHID = 'Buhid';
    public const CANADIAN_ABORIGINAL = 'Canadian_Aboriginal';
    public const CARIAN = 'Carian';
    public const CHAKMA = 'Chakma';
    public const CHAM = 'Cham';
    public const CHEROKEE = 'Cherokee';
    public const COMMON = 'Common';
    public const COPTIC = 'Coptic';
    public const CUNEIFORM = 'Cuneiform';
    public const CYPRIOT = 'Cypriot';
    public const CYRILLIC = 'Cyrillic';
    public const DESERET = 'Deseret';
    public const DEVANAGARI = 'Devanagari';
    public const EGYPTIAN_HIEROGLYPHS = 'Egyptian_Hieroglyphs';
    public const ETHIOPIC = 'Ethiopic';
    public const GEORGIAN = 'Georgian';
    public const GLAGOLITIC = 'Glagolitic';
    public const GOTHIC = 'Gothic';
    public const GREEK = 'Greek';
    public const GUJARATI = 'Gujarati';
    public const GURMUKHI = 'Gurmukhi';
    public const HAN = 'Han';
    public const HANGUL = 'Hangul';
    public const HANUNOO = 'Hanunoo';
    public const HEBREW = 'Hebrew';
    public const HIRAGANA = 'Hiragana';
    public const IMPERIAL_ARAMAIC = 'Imperial_Aramaic';
    public const INHERITED = 'Inherited';
    public const INSCRIPTIONAL_PAHLAVI = 'Inscriptional_Pahlavi';
    public const INSCRIPTIONAL_PARTHIAN = 'Inscriptional_Parthian';
    public const JAVANESE = 'Javanese';
    public const KAITHI = 'Kaithi';
    public const KANNADA = 'Kannada';
    public const KATAKANA = 'Katakana';
    public const KAYAH_LI = 'Kayah_Li';
    public const KHAROSHTHI = 'Kharoshthi';
    public const KHMER = 'Khmer';
    public const LAO = 'Lao';
    public const LATIN = 'Latin';
    public const LEPCHA = 'Lepcha';
    public const LIMBU = 'Limbu';
    public const LINEAR_B = 'Linear_B';
    public const LISU = 'Lisu';
    public const LYCIAN = 'Lycian';
    public const LYDIAN = 'Lydian';
    public const MALAYALAM = 'Malayalam';
    public const MANDAIC = 'Mandaic';
    public const MEETEI_MAYEK = 'Meetei_Mayek';
    public const MEROITIC_CURSIVE = 'Meroitic_Cursive';
    public const MEROITIC_HIEROGLYPHS = 'Meroitic_Hieroglyphs';
    public const MIAO = 'Miao';
    public const MONGOLIAN = 'Mongolian';
    public const MYANMAR = 'Myanmar';
    public const NEW_TAI_LUE = 'New_Tai_Lue';
    public const NKO = 'Nko';
    public const OGHAM = 'Ogham';
    public const OLD_ITALIC = 'Old_Italic';
    public const OLD_PERSIAN = 'Old_Persian';
    public const OLD_SOUTH_ARABIAN = 'Old_South_Arabian';
    public const OLD_TURKIC = 'Old_Turkic';
    public const OL_CHIKI = 'Ol_Chiki';
    public const ORIYA = 'Oriya';
    public const OSMANYA = 'Osmanya';
    public const PHAGS_PA = 'Phags_Pa';
    public const PHOENICIAN = 'Phoenician';
    public const REJANG = 'Rejang';
    public const RUNIC = 'Runic';
    public const SAMARITAN = 'Samaritan';
    public const SAURASHTRA = 'Saurashtra';
    public const SHARADA = 'Sharada';
    public const SHAVIAN = 'Shavian';
    public const SINHALA = 'Sinhala';
    public const SORA_SOMPENG = 'Sora_Sompeng';
    public const SUNDANESE = 'Sundanese';
    public const SYLOTI_NAGRI = 'Syloti_Nagri';
    public const SYRIAC = 'Syriac';
    public const TAGALOG = 'Tagalog';
    public const TAGBANWA = 'Tagbanwa';
    public const TAI_LE = 'Tai_Le';
    public const TAI_THAM = 'Tai_Tham';
    public const TAI_VIET = 'Tai_Viet';
    public const TAKRI = 'Takri';
    public const TAMIL = 'Tamil';
    public const TELUGU = 'Telugu';
    public const THAANA = 'Thaana';
    public const THAI = 'Thai';
    public const TIBETAN = 'Tibetan';
    public const TIFINAGH = 'Tifinagh';
    public const UGARITIC = 'Ugaritic';
    public const VAI = 'Vai';
    public const YI = 'Yi';

    /** @var string[] */
    private static array $iso = [
        self::ARABIC => Script::ARABIC,
        self::ARMENIAN => Script::ARMENIAN,
        self::AVESTAN => Script::AVESTAN,
        self::BALINESE => Script::BALINESE,
        self::BAMUM => Script::BAMUM,
        self::BATAK => Script::BATAK,
        self::BENGALI => Script::BENGALI,
        self::BOPOMOFO => Script::BOPOMOFO,
        self::BRAHMI => Script::BRAHMI,
        self::BRAILLE => Script::BRAILLE,
        self::BUGINESE => Script::BUGINESE,
        self::BUHID => Script::BUHID,
        self::CANADIAN_ABORIGINAL => Script::CANADIAN_ABORIGINAL_SYLLABICS,
        self::CARIAN => Script::CARIAN,
        self::CHAKMA => Script::CHAKMA,
        self::CHAM => Script::CHAM,
        self::CHEROKEE => Script::CHEROKEE,
        self::COMMON => Script::UNDETERMINED_SCRIPT,
        self::COPTIC => Script::COPTIC,
        self::CUNEIFORM => Script::CUNEIFORM,
        self::CYPRIOT => Script::CYPRIOT,
        self::CYRILLIC => Script::CYRILLIC,
        self::DESERET => Script::DESERET,
        self::DEVANAGARI => Script::DEVANAGARI,
        self::EGYPTIAN_HIEROGLYPHS => Script::EGYPTIAN_HIEROGLYPHS,
        self::ETHIOPIC => Script::ETHIOPIC,
        self::GEORGIAN => Script::GEORGIAN,
        self::GLAGOLITIC => Script::GLAGOLITIC,
        self::GOTHIC => Script::GOTHIC,
        self::GREEK => Script::GREEK,
        self::GUJARATI => Script::GUJARATI,
        self::GURMUKHI => Script::GURMUKHI,
        self::HAN => Script::HAN,
        self::HANGUL => Script::HANGUL,
        self::HANUNOO => Script::HANUNOO,
        self::HEBREW => Script::HEBREW,
        self::HIRAGANA => Script::HIRAGANA,
        self::IMPERIAL_ARAMAIC => Script::IMPERIAL_ARAMAIC,
        self::INHERITED => Script::INHERITED_SCRIPT,
        self::INSCRIPTIONAL_PAHLAVI => Script::INSCRIPTIONAL_PAHLAVI,
        self::INSCRIPTIONAL_PARTHIAN => Script::INSCRIPTIONAL_PARTHIAN,
        self::JAVANESE => Script::JAVANESE,
        self::KAITHI => Script::KAITHI,
        self::KANNADA => Script::KANNADA,
        self::KATAKANA => Script::KATAKANA,
        self::KAYAH_LI => Script::KAYAH_LI,
        self::KHAROSHTHI => Script::KHAROSHTHI,
        self::KHMER => Script::KHMER,
        self::LAO => Script::LAO,
        self::LATIN => Script::LATIN,
        self::LEPCHA => Script::LEPCHA,
        self::LIMBU => Script::LIMBU,
        self::LINEAR_B => Script::LINEAR_B,
        self::LISU => Script::LISU,
        self::LYCIAN => Script::LYCIAN,
        self::LYDIAN => Script::LYDIAN,
        self::MALAYALAM => Script::MALAYALAM,
        self::MANDAIC => Script::MANDAIC,
        self::MEETEI_MAYEK => Script::MEITEI_MAYEK,
        self::MEROITIC_CURSIVE => Script::MEROITIC_CURSIVE,
        self::MEROITIC_HIEROGLYPHS => Script::MEROITIC_HIEROGLYPHS,
        self::MIAO => Script::MIAO,
        self::MONGOLIAN => Script::MONGOLIAN,
        self::MYANMAR => Script::MYANMAR,
        self::NEW_TAI_LUE => Script::NEW_TAI_LUE,
        self::NKO => Script::NKO,
        self::OGHAM => Script::OGHAM,
        self::OLD_ITALIC => Script::OLD_ITALIC,
        self::OLD_PERSIAN => Script::OLD_PERSIAN,
        self::OLD_SOUTH_ARABIAN => Script::OLD_SOUTH_ARABIAN,
        self::OLD_TURKIC => Script::ORKHON_RUNIC,
        self::OL_CHIKI => Script::OL_CHIKI,
        self::ORIYA => Script::ORIYA,
        self::OSMANYA => Script::OSMANYA,
        self::PHAGS_PA => Script::PHAGS_PA,
        self::PHOENICIAN => Script::PHOENICIAN,
        self::REJANG => Script::REJANG,
        self::RUNIC => Script::RUNIC,
        self::SAMARITAN => Script::SAMARITAN,
        self::SAURASHTRA => Script::SAURASHTRA,
        self::SHARADA => Script::SHARADA,
        self::SHAVIAN => Script::SHAVIAN,
        self::SINHALA => Script::SINHALA,
        self::SORA_SOMPENG => Script::SORA_SOMPENG,
        self::SUNDANESE => Script::SUNDANESE,
        self::SYLOTI_NAGRI => Script::SYLOTI_NAGRI,
        self::SYRIAC => Script::SYRIAC,
        self::TAGALOG => Script::TAGALOG,
        self::TAGBANWA => Script::TAGBANWA,
        self::TAI_LE => Script::TAI_LE,
        self::TAI_THAM => Script::TAI_THAM,
        self::TAI_VIET => Script::TAI_VIET,
        self::TAKRI => Script::TAKRI,
        self::TAMIL => Script::TAMIL,
        self::TELUGU => Script::TELUGU,
        self::THAANA => Script::THAANA,
        self::THAI => Script::THAI,
        self::TIBETAN => Script::TIBETAN,
        self::TIFINAGH => Script::TIFINAGH,
        self::UGARITIC => Script::UGARITIC,
        self::VAI => Script::VAI,
        self::YI => Script::YI,
    ];

    public function getIsoCode(): string
    {
        return self::$iso[$this->getValue()];
    }

}
