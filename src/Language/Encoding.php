<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable Squiz.Arrays.ArrayDeclaration

namespace Dogma\Language;

use Dogma\Enum\StringEnum;
use function in_array;
use function strtoupper;

/**
 * Encoding codes accepted by mbstring and iconv (except BINARY; not all of them)
 */
class Encoding extends StringEnum
{

    public const BINARY = 'BINARY';

    public const ASCII = 'ASCII';

    public const UTF_8 = 'UTF-8';

    public const UTF_7 = 'UTF-7';
    public const UTF_7_IMAP = 'UTF7-IMAP';

    public const UTF_16 = 'UTF-16';
    public const UTF_16BE = 'UTF-16';
    public const UTF_16LE = 'UTF-16LE';

    public const UTF_32 = 'UTF-32';
    public const UTF_32BE = 'UTF-32';
    public const UTF_32LE = 'UTF-32LE';

    public const UCS_2 = 'UCS-2';
    public const UCS_2BE = 'UCS-2';
    public const UCS_2LE = 'UCS-2LE';

    public const UCS_4 = 'UCS-4';
    public const UCS_4BE = 'UCS-4';
    public const UCS_4LE = 'UCS-4LE';

    public const ISO_8859_1 = 'ISO-8859-1'; // Latin-1 Western European
    public const ISO_8859_2 = 'ISO-8859-2'; // Latin-2 Central European
    public const ISO_8859_3 = 'ISO-8859-3'; // Latin-3 South European
    public const ISO_8859_4 = 'ISO-8859-4'; // Latin-4 North European
    public const ISO_8859_5 = 'ISO-8859-5'; // Latin/Cyrillic
    public const ISO_8859_6 = 'ISO-8859-6'; // Latin/Arabic
    public const ISO_8859_7 = 'ISO-8859-7'; // Latin/Greek
    public const ISO_8859_8 = 'ISO-8859-8'; // Latin/Hebrew
    public const ISO_8859_9 = 'ISO-8859-9'; // Latin-5 Turkish
    public const ISO_8859_10 = 'ISO-8859-10'; // Latin-6 Nordic
    public const ISO_8859_11 = 'ISO-8859-11'; // Latin/Thai
    public const ISO_8859_13 = 'ISO-8859-13'; // Latin-7 Baltic Rim
    public const ISO_8859_14 = 'ISO-8859-14'; // Latin-8 Celtic
    public const ISO_8859_15 = 'ISO-8859-15'; // Latin-9
    public const ISO_8859_16 = 'ISO-8859-16'; // Latin-10 South-Eastern European

    public const WINDOWS_1250 = 'WINDOWS-1250'; // Latin 2 / Central European
    public const WINDOWS_1251 = 'WINDOWS-1251'; // Cyrillic
    public const WINDOWS_1252 = 'WINDOWS-1252'; // Latin 1 / Western European
    public const WINDOWS_1253 = 'WINDOWS-1253'; // Greek
    public const WINDOWS_1254 = 'WINDOWS-1254'; // Turkish
    public const WINDOWS_1255 = 'WINDOWS-1255'; // Hebrew
    public const WINDOWS_1256 = 'WINDOWS-1256'; // Arabic
    public const WINDOWS_1257 = 'WINDOWS-1257'; // Baltic
    public const WINDOWS_1258 = 'WINDOWS-1258'; // Vietnamese

    public const CP850 = 'CP850'; // DOS Latin 1 Western European
    public const CP852 = 'CP852'; // DOS Latin 2 Central European
    public const CP862 = 'CP862'; // DOS Hebrew
    public const CP866 = 'CP866'; // DOS Cyrillic
    public const CP932 = 'CP932'; // IBM SJIS
    public const CP936 = 'CP936'; // IBM Simplified Chinese
    public const CP950 = 'CP950'; // MS BIG-5

    public const CP50220 = 'CP50220';
    public const CP50221 = 'CP50221';
    public const CP50222 = 'CP50222';
    public const CP51932 = 'CP51932';

    public const EUC_JP = 'EUC-JP';
    public const EUC_JP_WIN = 'EUC-JP-WIN';
    public const EUC_JP_2004 = 'EUC-JP-2004';
    public const EUC_CN = 'EUC-CN';
    public const EUC_TW = 'EUC-TW';
    public const EUC_KR = 'EUC-KR';

    public const JIS = 'JIS';
    public const JIS_MS = 'JIS-MS';

    public const SJIS = 'SJIS';
    public const SJIS_WIN = 'SJIS-WIN';
    public const SJIS_MAC = 'SJIS-MAC';
    public const SJIS_2004 = 'SJIS-2004';

    public const ISO_2022_JP = 'ISO-2022-JP';
    public const ISO_2022_JP_MS = 'ISO-2022-JP-MS';
    public const ISO_2022_JP_2004 = 'ISO-2022-JP-2004';
    public const ISO_2022_KR = 'ISO-2022-KR';

    public const KOI8_R = 'KOI8-R';
    public const KOI8_U = 'KOI8-U';
    public const KOI8_T = 'KOI8-T';

    public const GB18030 = 'GB18030'; // 1-4 byte

    public const BIG_5 = 'BIG-5'; // 2 byte

    public const UHC = 'UHC'; // 2 byte

    public const HZ = 'HZ'; // ~{...~}

    public const ARMSCII_8 = 'ARMSCII-8';

    public static function isSupersetOfAscii(string $encoding): bool
    {
        static $asciiLike = [
            self::BINARY, self::ASCII, self::UTF_8,
            self::ISO_8859_1, self::ISO_8859_2, self::ISO_8859_3, self::ISO_8859_4, self::ISO_8859_5,
            self::ISO_8859_6, self::ISO_8859_7, self::ISO_8859_8, self::ISO_8859_9, self::ISO_8859_10,
            self::ISO_8859_11, self::ISO_8859_13, self::ISO_8859_14, self::ISO_8859_15, self::ISO_8859_16,
            self::WINDOWS_1250, self::WINDOWS_1251, self::WINDOWS_1252, self::WINDOWS_1253, self::WINDOWS_1254,
            self::WINDOWS_1255, self::WINDOWS_1256, self::WINDOWS_1257, self::WINDOWS_1258,
            self::ARMSCII_8,
        ];

        return in_array($encoding, $asciiLike, true);
    }

    public static function isVariableLength(string $encoding): bool
    {
        static $variable = [
            self::UTF_8, self::UTF_16, self::UTF_16LE, self::UTF_32, self::UTF_32LE,
            self::GB18030,
        ];

        return in_array($encoding, $variable, true);
    }

    public static function minLength(string $encoding): ?int
    {
        static $lengths = [
            self::UTF_16 => 2, self::UTF_16LE => 2,
            self::UTF_32 => 4, self::UTF_32LE => 4,
            self::BIG_5 => 2,
            self::UHC => 2,
        ];

        return $lengths[$encoding] ?? 1;
    }

    public static function getBom(string $encoding): ?string
    {
        static $bom = [
            self::UTF_8 => "\xEF\xBB\xBF",
            self::UTF_16 => "\xFE\xFF",
            self::UTF_16LE => "\xFF\xFE",
            self::UTF_32 => "\x00\x00\xFE\xFF",
            self::UTF_32LE => "\xFF\xFE\x00\x00",
        ];

        return $bom[$encoding] ?? null;
    }

    public static function validateValue(string &$value): bool
    {
        $value = strtoupper($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return 'BINARY|ASCII|UTF-(?:8|7(?:-IMAP)?|(?:(?:16|32)(?:BE|LE)?))|UCS-[24](?:BE|LE)'
            . '|ISO-8859-(?:1(0-6)?|[2-9])|WINDOWS-125[0-8]|CP[89]\\d\\d|CP5022[012]|CP51932'
            . '|EUC-(?:JP(?:-2004)?|CN|TW|KR)|EUC-JP-WIN|JIS(?:-MS)?|SJIS(?:-WIN|-MAC|2004)?'
            . '|ISO-2022-(?:JP(?:-MS|-2004)?|KR)|KOI8-[RUT]|GB18030|BIG-5|UHC|HZ|ARMSCII-8';
    }

}
