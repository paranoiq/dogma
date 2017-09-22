<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Charset;

use Dogma\Check;
use Dogma\Language\Encoding;
use Dogma\Language\Language;
use Dogma\Language\Locale\Locale;
use Dogma\Language\Locale\LocaleVariant;
use Dogma\Type;

class MysqlCollation extends \Dogma\Enum\PartialStringEnum
{

    public const BINARY = 'binary';

    public const ASCII_BIN = 'ascii_bin';
    public const ASCII_GENERAL = 'ascii_general_ci';

    public const UTF_8_BIN = 'utf8mb4_bin';
    public const UTF_8_GENERAL = 'utf8mb4_general_ci';
    public const UTF_8_UNICODE_520 = 'utf8mb4_unicode_520_ci';
    ///public const UTF_8_UNICODE_800 = 'utf8mb4_unicode_520_ci';

    public const UTF_8_CROATIAN = 'utf8mb4_croatian_ci';
    public const UTF_8_CZECH = 'utf8mb4_czech_ci';
    public const UTF_8_DANISH = 'utf8mb4_danish_ci';
    public const UTF_8_ESPERANTO = 'utf8mb4_esperanto_ci';
    public const UTF_8_ESTONIAN = 'utf8mb4_estonian_ci';
    public const UTF_8_GERMAN = 'utf8mb4_german2_ci';
    public const UTF_8_HUNGARIAN = 'utf8mb4_hungarian_ci';
    public const UTF_8_ICELANDIC = 'utf8mb4_icelandic_ci';
    public const UTF_8_LATVIAN = 'utf8mb4_latvian_ci';
    public const UTF_8_LITHUANIAN = 'utf8mb4_lithuanian_ci';
    public const UTF_8_PERSIAN = 'utf8mb4_persian_ci';
    public const UTF_8_POLISH = 'utf8mb4_polish_ci';
    public const UTF_8_ROMAN = 'utf8mb4_roman_ci';
    public const UTF_8_ROMANIAN = 'utf8mb4_romanian_ci';
    public const UTF_8_SINHALA = 'utf8mb4_sinhala_ci';
    public const UTF_8_SLOVAK = 'utf8mb4_slovak_ci';
    public const UTF_8_SLOVENIAN = 'utf8mb4_slovenian_ci';
    public const UTF_8_SPANISH = 'utf8mb4_spanish2_ci';
    public const UTF_8_SWEDISH = 'utf8mb4_swedish_ci';
    public const UTF_8_TURKISH = 'utf8mb4_turkish_ci';
    public const UTF_8_VIETNAMESE = 'utf8mb4_vietnamese_ci';

    /**
     * @param \Dogma\Language\Encoding|\Dogma\Database\Charset\MysqlCharset $charset
     * @param \Dogma\Language\Language|\Dogma\Language\Locale\Locale|\Dogma\Database\Charset\MysqlCollationType|null $collation
     * @return self
     */
    public function create($charset, $collation = null): self
    {
        Check::types($charset, [Encoding::class, MysqlCharset::class]);
        Check::types($collation, [Language::class, Locale::class, MysqlCollationType::class, Type::NULL]);

        if ($charset instanceof Encoding) {
            $charsetCode = MysqlCharset::fromEncoding($charset)->getValue();
        } else {
            $charsetCode = $charset->getValue();
        }
        if ($charsetCode === Encoding::BINARY) {
            return self::get(self::BINARY);
        }

        if ($collation instanceof MysqlCollationType) {
            $type = $collation->getValue();
            $language = null;
        } else {
            $type = $collation ? MysqlCollationType::LOCALE_CI : MysqlCollationType::UNICODE_CI;

            if ($collation instanceof Locale) {
                $language = $collation->getLanguage();
            } else {
                $language = $collation;
            }
        }

        if ($type === MysqlCollationType::LOCALE_CI) {
            $languageName = $language->getIdent();
            if (($languageName === 'german' || $languageName === 'spanish')
                && (!$collation instanceof Locale || !$collation->hasVariant(LocaleVariant::TRADITIONAL))
            ) {
                // use last version
                $languageName .= '2';
            }
            return self::get($charsetCode . '_' . $languageName . '_' . $type);
        } else {
            return self::get($charsetCode . '_' . $type);
        }
    }

    public static function validateValue(string &$value): bool
    {
        $value = strtolower($value);

        return parent::validateValue($value);
    }

    public static function getValueRegexp(): string
    {
        return 'binary|[a-z]{2}[a-z0-9]+(?:_bin|[a-z]+(?:2)?(?:_520|_mysql500)?_ci)';
    }

}
