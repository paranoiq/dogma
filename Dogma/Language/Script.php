<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

class Script extends \Dogma\Enum
{

    const ADLAM = 'Adlm';
    const AFAKA = 'Afak';
    const AHOM = 'Ahom';
    const ANATOLIAN_HIEROGLYPHS = 'Hluw';
    const ARABIC = 'Arab';
    const ARABIC_NASTALIQ = 'Aran';
    const ARMENIAN = 'Armn';
    const AVESTAN = 'Avst';
    const BALINESE = 'Bali';
    const BAMUM = 'Bamu';
    const BASSA_VAH = 'Bass';
    const BATAK = 'Batk';
    const BENGALI = 'Beng';
    const BHAIKSUKI = 'Bhks';
    const BLISSYMBOLS = 'Blis';
    const BOOK_PAHLAVI = 'Phlv';
    const BOPOMOFO = 'Bopo';
    const BRAHMI = 'Brah';
    const BRAILLE = 'Brai';
    const BUGINESE = 'Bugi';
    const BUHID = 'Buhd';
    const CANADIAN_ABORIGINAL_SYLLABICS = 'Cans';
    const CARIAN = 'Cari';
    const CAUCASIAN_ALBANIAN = 'Aghb';
    const CHAKMA = 'Cakm';
    const CHAM = 'Cham';
    const CHEROKEE = 'Cher';
    const CIRTH = 'Cirt';
    const COPTIC = 'Copt';
    const CUNEIFORM = 'Xsux';
    const CYPRIOT = 'Cprt';
    const CYRILLIC = 'Cyrl';
    const CYRILLIC_OLD_CHURCH_SLAVONIC = 'Cyrs';
    const DESERET = 'Dsrt';
    const DEVANAGARI = 'Deva';
    const DUPLOYAN_SHORTHAND = 'Dupl';
    const EGYPTIAN_DEMOTIC = 'Egyd';
    const EGYPTIAN_HIERATIC = 'Egyh';
    const EGYPTIAN_HIEROGLYPHS = 'Egyp';
    const ELBASAN = 'Elba';
    const ETHIOPIC = 'Ethi';
    const GEORGIAN = 'Geor';
    const GLAGOLITIC = 'Glag';
    const GOTHIC = 'Goth';
    const GRANTHA = 'Gran';
    const GREEK = 'Grek';
    const GUJARATI = 'Gujr';
    const GURMUKHI = 'Guru';
    const HAN = 'Hani';
    const HANGUL = 'Hang';
    const HANUNOO = 'Hano';
    const HAN_SIMPLIFIED = 'Hans';
    const HAN_TRADITIONAL = 'Hant';
    const HAN_WITH_BOPOMOFO = 'Hanb';
    const HATRAN = 'Hatr';
    const HEBREW = 'Hebr';
    const HIRAGANA = 'Hira';
    const IMPERIAL_ARAMAIC = 'Armi';
    const INDUS = 'Inds';
    const INHERITED_SCRIPT = 'Zinh';
    const INSCRIPTIONAL_PAHLAVI = 'Phli';
    const INSCRIPTIONAL_PARTHIAN = 'Prti';
    const JAMO = 'Jamo';
    const JAPANESE = 'Jpan';
    const JAPANESE_SYLLABARIES = 'Hrkt';
    const JAVANESE = 'Java';
    const JURCHEN = 'Jurc';
    const KAITHI = 'Kthi';
    const KANNADA = 'Knda';
    const KATAKANA = 'Kana';
    const KAYAH_LI = 'Kali';
    const KHAROSHTHI = 'Khar';
    const KHITAN_LARGE = 'Kitl';
    const KHITAN_SMALL = 'Kits';
    const KHMER = 'Khmr';
    const KHOJKI = 'Khoj';
    const KHUDAWADI = 'Sind';
    const KHUTSURI = 'Geok';
    const KLINGON = 'Piqd';
    const KOREAN = 'Kore';
    const KPELLE = 'Kpel';
    const LAO = 'Laoo';
    const LATIN = 'Latn';
    const LATIN_FRAKTUR = 'Latf';
    const LATIN_GAELIC = 'Latg';
    const LEKE = 'Leke';
    const LEPCHA = 'Lepc';
    const LIMBU = 'Limb';
    const LINEAR_A = 'Lina';
    const LINEAR_B = 'Linb';
    const LISU = 'Lisu';
    const LOMA = 'Loma';
    const LYCIAN = 'Lyci';
    const LYDIAN = 'Lydi';
    const MAHAJANI = 'Mahj';
    const MALAYALAM = 'Mlym';
    const MANDAIC = 'Mand';
    const MANICHAEAN = 'Mani';
    const MARCHEN = 'Marc';
    const MATHEMATICAL_NOTATION = 'Zmth';
    const MAYAN_HIEROGLYPHS = 'Maya';
    const MEITEI_MAYEK = 'Mtei';
    const MENDE_KIKAKUI = 'Mend';
    const MEROITIC_CURSIVE = 'Merc';
    const MEROITIC_HIEROGLYPHS = 'Mero';
    const MIAO = 'Plrd';
    const MODI = 'Modi';
    const MONGOLIAN = 'Mong';
    const MOON = 'Moon';
    const MRO = 'Mroo';
    const MULTANI = 'Mult';
    const MYANMAR = 'Mymr';
    const NABATAEAN = 'Nbat';
    const NAKHI_GEBA = 'Nkgb';
    const NEWA = 'Newa';
    const NEW_TAI_LUE = 'Talu';
    const NKO = 'Nkoo';
    const NUSHU = 'Nshu';
    const OGHAM = 'Ogam';
    const OLD_HUNGARIAN = 'Hung';
    const OLD_ITALIC = 'Ital';
    const OLD_NORTH_ARABIAN = 'Narb';
    const OLD_PERMIC = 'Perm';
    const OLD_PERSIAN = 'Xpeo';
    const OLD_SOUTH_ARABIAN = 'Sarb';
    const OL_CHIKI = 'Olck';
    const ORIYA = 'Orya';
    const ORKHON_RUNIC = 'Orkh';
    const OSAGE = 'Osge';
    const OSMANYA = 'Osma';
    const PAHAWH_HMONG = 'Hmng';
    const PALMYRENE = 'Palm';
    const PAU_CIN_HAU = 'Pauc';
    const PHAGS_PA = 'Phag';
    const PHOENICIAN = 'Phnx';
    const PSALTER_PAHLAVI = 'Phlp';
    const REJANG = 'Rjng';
    const RONGORONGO = 'Roro';
    const RUNIC = 'Runr';
    const SAMARITAN = 'Samr';
    const SARATI = 'Sara';
    const SAURASHTRA = 'Saur';
    const SHARADA = 'Shrd';
    const SHAVIAN = 'Shaw';
    const SIDDHAM = 'Sidd';
    const SIGNWRITING = 'Sgnw';
    const SINHALA = 'Sinh';
    const SORA_SOMPENG = 'Sora';
    const SUNDANESE = 'Sund';
    const SYLOTI_NAGRI = 'Sylo';
    const SYMBOLS = 'Zsym';
    const SYMBOLS_EMOJI = 'Zsye';
    const SYRIAC = 'Syrc';
    const SYRIAC_EASTERN = 'Syrn';
    const SYRIAC_ESTRANGELO = 'Syre';
    const SYRIAC_WESTERN = 'Syrj';
    const TAGALOG = 'Tglg';
    const TAGBANWA = 'Tagb';
    const TAI_LE = 'Tale';
    const TAI_THAM = 'Lana';
    const TAI_VIET = 'Tavt';
    const TAKRI = 'Takr';
    const TAMIL = 'Taml';
    const TANGUT = 'Tang';
    const TELUGU = 'Telu';
    const TENGWAR = 'Teng';
    const THAANA = 'Thaa';
    const THAI = 'Thai';
    const TIBETAN = 'Tibt';
    const TIFINAGH = 'Tfng';
    const TIRHUTA = 'Tirh';
    const UGARITIC = 'Ugar';
    const UNCODED_SCRIPT = 'Zzzz';
    const UNDETERMINED_SCRIPT = 'Zyyy';
    const UNWRITTEN_DOCUMENTS = 'Zxxx';
    const VAI = 'Vaii';
    const VISIBLE_SPEECH = 'Visp';
    const WARANG_CITI = 'Wara';
    const WOLEAI = 'Wole';
    const YI = 'Yiii';

    /** @var string[] */
    private static $names = [
        self::ADLAM => 'Adlam',
        self::AFAKA => 'Afaka',
        self::AHOM => 'Ahom, Tai Ahom',
        self::ANATOLIAN_HIEROGLYPHS => 'Anatolian Hieroglyphs (Luwian Hieroglyphs, Hittite Hieroglyphs)',
        self::ARABIC => 'Arabic',
        self::ARABIC_NASTALIQ => 'Arabic (Nastaliq variant)',
        self::ARMENIAN => 'Armenian',
        self::AVESTAN => 'Avestan',
        self::BALINESE => 'Balinese',
        self::BAMUM => 'Bamum',
        self::BASSA_VAH => 'Bassa Vah',
        self::BATAK => 'Batak',
        self::BENGALI => 'Bengali',
        self::BHAIKSUKI => 'Bhaiksuki',
        self::BLISSYMBOLS => 'Blissymbols',
        self::BOOK_PAHLAVI => 'Book Pahlavi',
        self::BOPOMOFO => 'Bopomofo',
        self::BRAHMI => 'Brahmi',
        self::BRAILLE => 'Braille',
        self::BUGINESE => 'Buginese',
        self::BUHID => 'Buhid',
        self::CANADIAN_ABORIGINAL_SYLLABICS => 'Unified Canadian Aboriginal Syllabics',
        self::CARIAN => 'Carian',
        self::CAUCASIAN_ALBANIAN => 'Caucasian Albanian',
        self::CHAKMA => 'Chakma',
        self::CHAM => 'Cham',
        self::CHEROKEE => 'Cherokee',
        self::CIRTH => 'Cirth',
        self::COPTIC => 'Coptic',
        self::CUNEIFORM => 'Cuneiform, Sumero-Akkadian',
        self::CYPRIOT => 'Cypriot',
        self::CYRILLIC => 'Cyrillic',
        self::CYRILLIC_OLD_CHURCH_SLAVONIC => 'Cyrillic (Old Church Slavonic variant)',
        self::DESERET => 'Deseret (Mormon)',
        self::DEVANAGARI => 'Devanagari (Nagari)',
        self::DUPLOYAN_SHORTHAND => 'Duployan shorthand, Duployan stenography',
        self::EGYPTIAN_DEMOTIC => 'Egyptian demotic',
        self::EGYPTIAN_HIERATIC => 'Egyptian hieratic',
        self::EGYPTIAN_HIEROGLYPHS => 'Egyptian hieroglyphs',
        self::ELBASAN => 'Elbasan',
        self::ETHIOPIC => 'Ethiopic (Geʻez)',
        self::GEORGIAN => 'Georgian (Mkhedruli)',
        self::GLAGOLITIC => 'Glagolitic',
        self::GOTHIC => 'Gothic',
        self::GRANTHA => 'Grantha',
        self::GREEK => 'Greek',
        self::GUJARATI => 'Gujarati',
        self::GURMUKHI => 'Gurmukhi',
        self::HAN => 'Han (Hanzi, Kanji, Hanja)',
        self::HANGUL => 'Hangul (Hangŭl, Hangeul)',
        self::HANUNOO => 'Hanunoo (Hanunóo)',
        self::HAN_SIMPLIFIED => 'Han (Simplified variant)',
        self::HAN_TRADITIONAL => 'Han (Traditional variant)',
        self::HAN_WITH_BOPOMOFO => 'Han with Bopomofo (alias for Han + Bopomofo)',
        self::HATRAN => 'Hatran',
        self::HEBREW => 'Hebrew',
        self::HIRAGANA => 'Hiragana',
        self::IMPERIAL_ARAMAIC => 'Imperial Aramaic',
        self::INDUS => 'Indus (Harappan)',
        self::INHERITED_SCRIPT => 'Code for inherited script',
        self::INSCRIPTIONAL_PAHLAVI => 'Inscriptional Pahlavi',
        self::INSCRIPTIONAL_PARTHIAN => 'Inscriptional Parthian',
        self::JAMO => 'Jamo (alias for Jamo subset of Hangul)',
        self::JAPANESE => 'Japanese (alias for Han + Hiragana + Katakana)',
        self::JAPANESE_SYLLABARIES => 'Japanese syllabaries (alias for Hiragana + Katakana)',
        self::JAVANESE => 'Javanese',
        self::JURCHEN => 'Jurchen',
        self::KAITHI => 'Kaithi',
        self::KANNADA => 'Kannada',
        self::KATAKANA => 'Katakana',
        self::KAYAH_LI => 'Kayah Li',
        self::KHAROSHTHI => 'Kharoshthi',
        self::KHITAN_LARGE => 'Khitan large script',
        self::KHITAN_SMALL => 'Khitan small script',
        self::KHMER => 'Khmer',
        self::KHOJKI => 'Khojki',
        self::KHUDAWADI => 'Khudawadi, Sindhi',
        self::KHUTSURI => 'Khutsuri (Asomtavruli and Nuskhuri)',
        self::KLINGON => 'Klingon (KLI pIqaD)',
        self::KOREAN => 'Korean (alias for Hangul + Han)',
        self::KPELLE => 'Kpelle',
        self::LAO => 'Lao',
        self::LATIN => 'Latin',
        self::LATIN_FRAKTUR => 'Latin (Fraktur variant)',
        self::LATIN_GAELIC => 'Latin (Gaelic variant)',
        self::LEKE => 'Leke',
        self::LEPCHA => 'Lepcha (Róng)',
        self::LIMBU => 'Limbu',
        self::LINEAR_A => 'Linear A',
        self::LINEAR_B => 'Linear B',
        self::LISU => 'Lisu (Fraser)',
        self::LOMA => 'Loma',
        self::LYCIAN => 'Lycian',
        self::LYDIAN => 'Lydian',
        self::MAHAJANI => 'Mahajani',
        self::MALAYALAM => 'Malayalam',
        self::MANDAIC => 'Mandaic, Mandaean',
        self::MANICHAEAN => 'Manichaean',
        self::MARCHEN => 'Marchen',
        self::MATHEMATICAL_NOTATION => 'Mathematical notation',
        self::MAYAN_HIEROGLYPHS => 'Mayan hieroglyphs',
        self::MEITEI_MAYEK => 'Meitei Mayek (Meithei, Meetei)',
        self::MENDE_KIKAKUI => 'Mende Kikakui',
        self::MEROITIC_CURSIVE => 'Meroitic Cursive',
        self::MEROITIC_HIEROGLYPHS => 'Meroitic Hieroglyphs',
        self::MIAO => 'Miao (Pollard)',
        self::MODI => 'Modi, Moḍī',
        self::MONGOLIAN => 'Mongolian',
        self::MOON => 'Moon (Moon code, Moon script, Moon type)',
        self::MRO => 'Mro, Mru',
        self::MULTANI => 'Multani',
        self::MYANMAR => 'Myanmar (Burmese)',
        self::NABATAEAN => 'Nabataean',
        self::NAKHI_GEBA => 'Nakhi Geba (\'Na-\'Khi ²Ggŏ-¹baw, Naxi Geba)',
        self::NEWA => 'Newa, Newar, Newari, Nepāla lipi',
        self::NEW_TAI_LUE => 'New Tai Lue',
        self::NKO => 'N’Ko',
        self::NUSHU => 'Nüshu',
        self::OGHAM => 'Ogham',
        self::OLD_HUNGARIAN => 'Old Hungarian (Hungarian Runic)',
        self::OLD_ITALIC => 'Old Italic (Etruscan, Oscan, etc.)',
        self::OLD_NORTH_ARABIAN => 'Old North Arabian (Ancient North Arabian)',
        self::OLD_PERMIC => 'Old Permic',
        self::OLD_PERSIAN => 'Old Persian',
        self::OLD_SOUTH_ARABIAN => 'Old South Arabian',
        self::OL_CHIKI => 'Ol Chiki (Ol Cemet’, Ol, Santali)',
        self::ORIYA => 'Oriya',
        self::ORKHON_RUNIC => 'Old Turkic, Orkhon Runic',
        self::OSAGE => 'Osage',
        self::OSMANYA => 'Osmanya',
        self::PAHAWH_HMONG => 'Pahawh Hmong',
        self::PALMYRENE => 'Palmyrene',
        self::PAU_CIN_HAU => 'Pau Cin Hau',
        self::PHAGS_PA => 'Phags-pa',
        self::PHOENICIAN => 'Phoenician',
        self::PSALTER_PAHLAVI => 'Psalter Pahlavi',
        self::REJANG => 'Rejang (Redjang, Kaganga)',
        self::RONGORONGO => 'Rongorongo',
        self::RUNIC => 'Runic',
        self::SAMARITAN => 'Samaritan',
        self::SARATI => 'Sarati',
        self::SAURASHTRA => 'Saurashtra',
        self::SHARADA => 'Sharada, Śāradā',
        self::SHAVIAN => 'Shavian (Shaw)',
        self::SIDDHAM => 'Siddham, Siddhaṃ, Siddhamātṛkā',
        self::SIGNWRITING => 'SignWriting',
        self::SINHALA => 'Sinhala',
        self::SORA_SOMPENG => 'Sora Sompeng',
        self::SUNDANESE => 'Sundanese',
        self::SYLOTI_NAGRI => 'Syloti Nagri',
        self::SYMBOLS => 'Symbols',
        self::SYMBOLS_EMOJI => 'Symbols (Emoji variant)',
        self::SYRIAC => 'Syriac',
        self::SYRIAC_EASTERN => 'Syriac (Eastern variant)',
        self::SYRIAC_ESTRANGELO => 'Syriac (Estrangelo variant)',
        self::SYRIAC_WESTERN => 'Syriac (Western variant)',
        self::TAGALOG => 'Tagalog (Baybayin, Alibata)',
        self::TAGBANWA => 'Tagbanwa',
        self::TAI_LE => 'Tai Le',
        self::TAI_THAM => 'Tai Tham (Lanna)',
        self::TAI_VIET => 'Tai Viet',
        self::TAKRI => 'Takri, Ṭākrī, Ṭāṅkrī',
        self::TAMIL => 'Tamil',
        self::TANGUT => 'Tangut',
        self::TELUGU => 'Telugu',
        self::TENGWAR => 'Tengwar',
        self::THAANA => 'Thaana',
        self::THAI => 'Thai',
        self::TIBETAN => 'Tibetan',
        self::TIFINAGH => 'Tifinagh (Berber)',
        self::TIRHUTA => 'Tirhuta',
        self::UGARITIC => 'Ugaritic',
        self::UNCODED_SCRIPT => 'Code for uncoded script',
        self::UNDETERMINED_SCRIPT => 'Code for undetermined script',
        self::UNWRITTEN_DOCUMENTS => 'Code for unwritten documents',
        self::VAI => 'Vai',
        self::VISIBLE_SPEECH => 'Visible Speech',
        self::WARANG_CITI => 'Warang Citi (Varang Kshiti)',
        self::WOLEAI => 'Woleai',
        self::YI => 'Yi',
    ];

    /** @var string[] */
    private static $idents = [
        self::ADLAM => 'adlam',
        self::AFAKA => 'afaka',
        self::AHOM => 'ahom',
        self::ANATOLIAN_HIEROGLYPHS => 'anatolian-hieroglyphs',
        self::ARABIC => 'arabic',
        self::ARABIC_NASTALIQ => 'arabic-nastaliq',
        self::ARMENIAN => 'armenian',
        self::AVESTAN => 'avestan',
        self::BALINESE => 'balinese',
        self::BAMUM => 'bamum',
        self::BASSA_VAH => 'bassa-vah',
        self::BATAK => 'batak',
        self::BENGALI => 'bengali',
        self::BHAIKSUKI => 'bhaiksuki',
        self::BLISSYMBOLS => 'blissymbols',
        self::BOOK_PAHLAVI => 'book-pahlavi',
        self::BOPOMOFO => 'bopomofo',
        self::BRAHMI => 'brahmi',
        self::BRAILLE => 'braille',
        self::BUGINESE => 'buginese',
        self::BUHID => 'buhid',
        self::CANADIAN_ABORIGINAL_SYLLABICS => 'canadian-aboriginal-syllabics',
        self::CARIAN => 'carian',
        self::CAUCASIAN_ALBANIAN => 'caucasian-albanian',
        self::CHAKMA => 'chakma',
        self::CHAM => 'cham',
        self::CHEROKEE => 'cherokee',
        self::CIRTH => 'cirth',
        self::COPTIC => 'coptic',
        self::CUNEIFORM => 'cuneiform',
        self::CYPRIOT => 'cypriot',
        self::CYRILLIC => 'cyrillic',
        self::CYRILLIC_OLD_CHURCH_SLAVONIC => 'cyrillic-old-church-slavonic',
        self::DESERET => 'deseret',
        self::DEVANAGARI => 'devanagari',
        self::DUPLOYAN_SHORTHAND => 'duployan-shorthand',
        self::EGYPTIAN_DEMOTIC => 'egyptian-demotic',
        self::EGYPTIAN_HIERATIC => 'egyptian-hieratic',
        self::EGYPTIAN_HIEROGLYPHS => 'egyptian-hieroglyphs',
        self::ELBASAN => 'elbasan',
        self::ETHIOPIC => 'ethiopic',
        self::GEORGIAN => 'georgian',
        self::GLAGOLITIC => 'glagolitic',
        self::GOTHIC => 'gothic',
        self::GRANTHA => 'grantha',
        self::GREEK => 'greek',
        self::GUJARATI => 'gujarati',
        self::GURMUKHI => 'gurmukhi',
        self::HAN => 'han',
        self::HANGUL => 'hangul',
        self::HANUNOO => 'hanunoo',
        self::HAN_SIMPLIFIED => 'han-simplified',
        self::HAN_TRADITIONAL => 'han-traditional',
        self::HAN_WITH_BOPOMOFO => 'han-with-bopomofo',
        self::HATRAN => 'hatran',
        self::HEBREW => 'hebrew',
        self::HIRAGANA => 'hiragana',
        self::IMPERIAL_ARAMAIC => 'imperial-aramaic',
        self::INDUS => 'indus',
        self::INHERITED_SCRIPT => 'inherited-script',
        self::INSCRIPTIONAL_PAHLAVI => 'inscriptional-pahlavi',
        self::INSCRIPTIONAL_PARTHIAN => 'inscriptional-parthian',
        self::JAMO => 'jamo',
        self::JAPANESE => 'japanese',
        self::JAPANESE_SYLLABARIES => 'japanese-syllabaries',
        self::JAVANESE => 'javanese',
        self::JURCHEN => 'jurchen',
        self::KAITHI => 'kaithi',
        self::KANNADA => 'kannada',
        self::KATAKANA => 'katakana',
        self::KAYAH_LI => 'kayah-li',
        self::KHAROSHTHI => 'kharoshthi',
        self::KHITAN_LARGE => 'khitan-large',
        self::KHITAN_SMALL => 'khitan-small',
        self::KHMER => 'khmer',
        self::KHOJKI => 'khojki',
        self::KHUDAWADI => 'khudawadi',
        self::KHUTSURI => 'khutsuri',
        self::KLINGON => 'klingon',
        self::KOREAN => 'korean',
        self::KPELLE => 'kpelle',
        self::LAO => 'lao',
        self::LATIN => 'latin',
        self::LATIN_FRAKTUR => 'latin-fraktur',
        self::LATIN_GAELIC => 'latin-gaelic',
        self::LEKE => 'leke',
        self::LEPCHA => 'lepcha',
        self::LIMBU => 'limbu',
        self::LINEAR_A => 'linear-a',
        self::LINEAR_B => 'linear-b',
        self::LISU => 'lisu',
        self::LOMA => 'loma',
        self::LYCIAN => 'lycian',
        self::LYDIAN => 'lydian',
        self::MAHAJANI => 'mahajani',
        self::MALAYALAM => 'malayalam',
        self::MANDAIC => 'mandaic',
        self::MANICHAEAN => 'manichaean',
        self::MARCHEN => 'marchen',
        self::MATHEMATICAL_NOTATION => 'mathematical-notation',
        self::MAYAN_HIEROGLYPHS => 'mayan-hieroglyphs',
        self::MEITEI_MAYEK => 'meitei-mayek',
        self::MENDE_KIKAKUI => 'mende-kikakui',
        self::MEROITIC_CURSIVE => 'meroitic-cursive',
        self::MEROITIC_HIEROGLYPHS => 'meroitic-hieroglyphs',
        self::MIAO => 'miao',
        self::MODI => 'modi',
        self::MONGOLIAN => 'mongolian',
        self::MOON => 'moon',
        self::MRO => 'mro',
        self::MULTANI => 'multani',
        self::MYANMAR => 'myanmar',
        self::NABATAEAN => 'nabataean',
        self::NAKHI_GEBA => 'nakhi-geba',
        self::NEWA => 'newa',
        self::NEW_TAI_LUE => 'new-tai-lue',
        self::NKO => 'nko',
        self::NUSHU => 'nushu',
        self::OGHAM => 'ogham',
        self::OLD_HUNGARIAN => 'old-hungarian',
        self::OLD_ITALIC => 'old-italic',
        self::OLD_NORTH_ARABIAN => 'old-north-arabian',
        self::OLD_PERMIC => 'old-permic',
        self::OLD_PERSIAN => 'old-persian',
        self::OLD_SOUTH_ARABIAN => 'old-south-arabian',
        self::OL_CHIKI => 'ol-chiki',
        self::ORIYA => 'oriya',
        self::ORKHON_RUNIC => 'orkhon-runic',
        self::OSAGE => 'osage',
        self::OSMANYA => 'osmanya',
        self::PAHAWH_HMONG => 'pahawh-hmong',
        self::PALMYRENE => 'palmyrene',
        self::PAU_CIN_HAU => 'pau-cin-hau',
        self::PHAGS_PA => 'phags-pa',
        self::PHOENICIAN => 'phoenician',
        self::PSALTER_PAHLAVI => 'psalter-pahlavi',
        self::REJANG => 'rejang',
        self::RONGORONGO => 'rongorongo',
        self::RUNIC => 'runic',
        self::SAMARITAN => 'samaritan',
        self::SARATI => 'sarati',
        self::SAURASHTRA => 'saurashtra',
        self::SHARADA => 'sharada',
        self::SHAVIAN => 'shavian',
        self::SIDDHAM => 'siddham',
        self::SIGNWRITING => 'signwriting',
        self::SINHALA => 'sinhala',
        self::SORA_SOMPENG => 'sora-sompeng',
        self::SUNDANESE => 'sundanese',
        self::SYLOTI_NAGRI => 'syloti-nagri',
        self::SYMBOLS => 'symbols',
        self::SYMBOLS_EMOJI => 'symbols-emoji',
        self::SYRIAC => 'syriac',
        self::SYRIAC_EASTERN => 'syriac-eastern',
        self::SYRIAC_ESTRANGELO => 'syriac-estrangelo',
        self::SYRIAC_WESTERN => 'syriac-western',
        self::TAGALOG => 'tagalog',
        self::TAGBANWA => 'tagbanwa',
        self::TAI_LE => 'tai-le',
        self::TAI_THAM => 'tai-tham',
        self::TAI_VIET => 'tai-viet',
        self::TAKRI => 'takri',
        self::TAMIL => 'tamil',
        self::TANGUT => 'tangut',
        self::TELUGU => 'telugu',
        self::TENGWAR => 'tengwar',
        self::THAANA => 'thaana',
        self::THAI => 'thai',
        self::TIBETAN => 'tibetan',
        self::TIFINAGH => 'tifinagh',
        self::TIRHUTA => 'tirhuta',
        self::UGARITIC => 'ugaritic',
        self::UNCODED_SCRIPT => 'uncoded-script',
        self::UNDETERMINED_SCRIPT => 'undetermined-script',
        self::UNWRITTEN_DOCUMENTS => 'unwritten-documents',
        self::VAI => 'vai',
        self::VISIBLE_SPEECH => 'visible-speech',
        self::WARANG_CITI => 'warang-citi',
        self::WOLEAI => 'woleai',
        self::YI => 'yi',
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    public function getIdent(): string
    {
        return self::$idents[$this->getValue()];
    }

    public function getByIdent(string $ident): self
    {
        return self::get(array_search($ident, self::$idents));
    }

    public static function validateValue(&$value): bool
    {
        $value = ucfirst(strtolower($value));

        return parent::validateValue($value);
    }

}