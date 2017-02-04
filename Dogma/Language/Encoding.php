<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

/**
 * Encoding codes accepted by mbstring and iconv (except BINARY; not all of them)
 */
class Encoding extends \Dogma\Enum
{

    const BINARY = 'BINARY';

    const ASCII = 'ASCII';

    const UTF_8 = 'UTF-8';

    const UTF_7 = 'UTF-7';
    const UTF_7_IMAP = 'UTF7-IMAP';

    const UTF_16 = 'UTF-16';
    const UTF_16BE = 'UTF-16BE';
    const UTF_16LE = 'UTF-16LE';

    const UTF_32 = 'UTF-32';
    const UTF_32BE = 'UTF-32BE';
    const UTF_32LE = 'UTF-32LE';

    const UCS_2 = 'UCS-2';
    const UCS_2BE = 'UCS-2BE';
    const UCS_2LE = 'UCS-2LE';

    const UCS_4 = 'UCS-4';
    const UCS_4BE = 'UCS-4BE';
    const UCS_4LE = 'UCS-4LE';

    const ISO_8859_1 = 'ISO-8859-1'; // Latin-1 Western European
    const ISO_8859_2 = 'ISO-8859-2'; // Latin-2 Central European
    const ISO_8859_3 = 'ISO-8859-3'; // Latin-3 South European
    const ISO_8859_4 = 'ISO-8859-4'; // Latin-4 North European
    const ISO_8859_5 = 'ISO-8859-5'; // Latin/Cyrillic
    const ISO_8859_6 = 'ISO-8859-6'; // Latin/Arabic
    const ISO_8859_7 = 'ISO-8859-7'; // Latin/Greek
    const ISO_8859_8 = 'ISO-8859-8'; // Latin/Hebrew
    const ISO_8859_9 = 'ISO-8859-9'; // Latin-5 Turkish
    const ISO_8859_10 = 'ISO-8859-10'; // Latin-6 Nordic
    const ISO_8859_11 = 'ISO-8859-11'; // Latin/Thai
    const ISO_8859_13 = 'ISO-8859-13'; // Latin-7 Baltic Rim
    const ISO_8859_14 = 'ISO-8859-14'; // Latin-8 Celtic
    const ISO_8859_15 = 'ISO-8859-15'; // Latin-9
    const ISO_8859_16 = 'ISO-8859-16'; // Latin-10 South-Eastern European

    const WINDOWS_1250 = 'WINDOWS-1250'; // Latin 2 / Central European
    const WINDOWS_1251 = 'WINDOWS-1251'; // Cyrillic
    const WINDOWS_1252 = 'WINDOWS-1252'; // Latin 1 / Western European
    const WINDOWS_1253 = 'WINDOWS-1253'; // Greek
    const WINDOWS_1254 = 'WINDOWS-1254'; // Turkish
    const WINDOWS_1255 = 'WINDOWS-1255'; // Hebrew
    const WINDOWS_1256 = 'WINDOWS-1256'; // Arabic
    const WINDOWS_1257 = 'WINDOWS-1257'; // Baltic
    const WINDOWS_1258 = 'WINDOWS-1258'; // Vietnamese

    const CP850 = 'CP850'; // DOS Latin 1 Western European
    const CP852 = 'CP852'; // DOS Latin 2 Central European
    const CP862 = 'CP862'; // DOS Hebrew
    const CP866 = 'CP866'; // DOS Cyrillic
    const CP932 = 'CP932'; // IBM SJIS
    const CP936 = 'CP936'; // IBM Simplified Chinese
    const CP950 = 'CP950'; // MS BIG-5

    const CP50220 = 'CP50220';
    const CP50221 = 'CP50221';
    const CP50222 = 'CP50222';
    const CP51932 = 'CP51932';

    const EUC_JP = 'EUC-JP';
    const EUC_JP_WIN = 'EUC-JP-WIN';
    const EUC_JP_2004 = 'EUC-JP-2004';
    const EUC_CN = 'EUC-CN';
    const EUC_TW = 'EUC-TW';
    const EUC_KR = 'EUC-KR';

    const JIS = 'JIS';
    const JIS_MS = 'JIS-MS';

    const SJIS = 'SJIS';
    const SJIS_WIN = 'SJIS-WIN';
    const SJIS_MAC = 'SJIS-MAC';
    const SJIS_2004 = 'SJIS-2004';

    const ISO_2022_JP = 'ISO-2022-JP';
    const ISO_2022_JP_MS = 'ISO-2022-JP-MS';
    const ISO_2022_JP_2004 = 'ISO-2022-JP-2004';
    const ISO_2022_KR = 'ISO-2022-KR';

    const KOI8_R = 'KOI8-R';
    const KOI8_U = 'KOI8-U';
    const KOI8_T = 'KOI8-T';

    const GB18030 = 'GB18030';

    const BIG_5 = 'BIG-5';

    const UHC = 'UHC';

    const HZ = 'HZ';

    const ARMSCII_8 = 'ARMSCII-8';

    public static function validateValue(&$value): bool
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
