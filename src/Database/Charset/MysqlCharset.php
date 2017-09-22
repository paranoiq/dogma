<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Charset;

use Dogma\Language\Encoding;

class MysqlCharset extends \Dogma\Enum\StringEnum
{

    public const ARMSCII_8 = 'armscii8';
    public const ASCII = 'ascii';
    public const BIG_5 = 'big5';
    public const BINARY = 'binary';
    public const CP1250 = 'cp1250';
    public const CP1251 = 'cp1251';
    public const CP1256 = 'cp1256';
    public const CP1257 = 'cp1257';
    public const CP850 = 'cp850';
    public const CP852 = 'cp852';
    public const CP866 = 'cp866';
    public const CP932 = 'cp932';
    public const DEC_8 = 'dec8';
    public const EUC_JP_MS = 'eucjpms';
    public const EUC_KR = 'euckr';
    public const GB18030 = 'gb18030';
    public const GB2312 = 'gb2312';
    public const GBK = 'gbk';
    public const GEOSTD_8 = 'geostd8';
    public const GREEK = 'greek';
    public const HEBREW = 'hebrew';
    public const HP_8 = 'hp8';
    public const KEYBCS_2 = 'keybcs2';
    public const KOI8_R = 'koi8r';
    public const KOI8_U = 'koi8u';
    public const LATIN_1 = 'latin1';
    public const LATIN_2 = 'latin2';
    public const LATIN_5 = 'latin5';
    public const LATIN_7 = 'latin7';
    public const MAC_CE = 'macce';
    public const MAC_ROMAN = 'macroman';
    public const SJIS = 'sjis';
    public const SWE_7 = 'swe7';
    public const TIS_620 = 'tis620';
    public const UCS_2 = 'ucs2';
    public const UJIS = 'ujis';
    public const UTF_16 = 'utf16';
    public const UTF_16LE = 'utf16le';
    public const UTF_32 = 'utf32';
    public const UTF_8_OLD = 'utf8';
    public const UTF_8 = 'utf8mb4';

    /** @var string[] */
    private static $mapping = [
        Encoding::ARMSCII_8 => self::ARMSCII_8,
        Encoding::ASCII => self::ASCII,
        Encoding::BIG_5 => self::BIG_5,
        Encoding::BINARY => self::BINARY,
        Encoding::WINDOWS_1250 => self::CP1250,
        Encoding::WINDOWS_1251 => self::CP1251,
        Encoding::WINDOWS_1256 => self::CP1256,
        Encoding::WINDOWS_1257 => self::CP1257,
        Encoding::CP850 => self::CP850,
        Encoding::CP852 => self::CP852,
        Encoding::CP866 => self::CP866,
        Encoding::CP932 => self::CP932,
        // DEC_8
        Encoding::EUC_JP_WIN => self::EUC_JP_MS,
        Encoding::EUC_KR => self::EUC_KR,
        Encoding::GB18030 => self::GB18030,
        // GB2312
        // GBK
        // GEOSTD_8
        Encoding::ISO_8859_7 => self::GREEK,
        Encoding::ISO_8859_8 => self::HEBREW,
        // HP_8
        // KEYBCS_2
        Encoding::KOI8_R => self::KOI8_R,
        Encoding::KOI8_U => self::KOI8_U,
        Encoding::ISO_8859_1 => self::LATIN_1,
        Encoding::ISO_8859_2 => self::LATIN_2,
        Encoding::ISO_8859_9 => self::LATIN_5,
        Encoding::ISO_8859_13 => self::LATIN_7,
        // MAC_CE
        // MAC_ROMAN
        Encoding::SJIS => self::SJIS,
        // SWE_7
        // TIS_620
        Encoding::UCS_2 => self::UCS_2,
        // UJIS
        Encoding::UTF_16 => self::UTF_16,
        Encoding::UTF_16BE => self::UTF_16,
        Encoding::UTF_16LE => self::UTF_16LE,
        Encoding::UTF_32 => self::UTF_32,
        // UTF_8_OLD
        Encoding::UTF_8 => self::UTF_8,
    ];

    public static function fromEncoding(Encoding $encoding): self
    {
        if (!isset(self::$mapping[$encoding->getValue()])) {
            throw new \Dogma\InvalidValueException($encoding->getValue(), __CLASS__);
        }
        return self::get(self::$mapping[$encoding->getValue()]);
    }

}
