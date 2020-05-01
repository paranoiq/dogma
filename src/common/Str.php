<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Dogma\Language\Collator;
use Dogma\Language\Locale\Locale;
use Dogma\Language\Transliterator;
use Dogma\Language\UnicodeCharacterCategory;
use Nette\Utils\Strings;
use const MB_CASE_TITLE;
use function is_string;
use function mb_convert_case;
use function mb_strlen;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function min;
use function range;
use function str_replace;
use function strcasecmp;
use function strcmp;
use function strlen;
use function strncmp;
use function strpos;
use function substr;

/**
 * UTF-8 strings manipulation
 */
class Str
{
    use StaticClassMixin;

    /**
     * Test equality with another string
     * @param string $first
     * @param string $second
     * @param string|\Collator|\Dogma\Language\Locale\Locale $collation
     * @return bool
     */
    public static function equals(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): bool
    {
        return self::compare($first, $second, $collation) === 0;
    }

    /**
     * Compare to another string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $first
     * @param string $second
     * @param string|\Collator|\Dogma\Language\Locale\Locale $collation
     * @return int
     */
    public static function compare($first, $second, $collation = CaseComparison::CASE_SENSITIVE): int
    {
        if ($collation === CaseComparison::CASE_SENSITIVE) {
            return strcmp($first, $second);
        } elseif ($collation === CaseComparison::CASE_INSENSITIVE) {
            return strcasecmp($first, $second);
        } elseif (is_string($collation) || $collation instanceof Locale) {
            $collation = new Collator($collation);
        } elseif (!$collation instanceof \Collator) {
            throw new InvalidValueException($collation, [Type::STRING, \Collator::class, Locale::class]);
        }
        return $collation->compare($first, $second);
    }

    public static function substringCount(string $string, string $substring): int
    {
        return (strlen($string) - strlen(str_replace($substring, '', $string))) / strlen($substring);
    }

    public static function between(string $string, string $from, string $to): ?string
    {
        $after = self::after($string, $from);
        if ($after === false) {
            return null;
        }
        $before = self::before($after, $to);
        if ($after === false) {
            return null;
        }
        return $before;
    }

    public static function toFirst(string $string, string $search): string
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return $string;
        }

        return substr($string, 0, $pos);
    }

    public static function fromFirst(string $string, string $search): string
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return '';
        }

        return substr($string, $pos + 1);
    }

    /**
     * @param string $string
     * @param string $search
     * @return string[]
     */
    public static function splitByFirst(string $string, string $search): array
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return [$string, ''];
        }

        return [substr($string, 0, $pos), substr($string, $pos + 1)];
    }

    /**
     * Levenshtein distance for UTF-8 with additional weights for accent and case differences.
     * Expects input strings to be normalized UTF-8.
     *
     * @param string $string1
     * @param string $string2
     * @param float $insertionCost
     * @param float $deletionCost
     * @param float $replacementCost
     * @param float|null $replacementAccentCost
     * @param float|null $replacementCaseCost
     * @return float
     */
    public static function levenshteinUnicode(
        string $string1,
        string $string2,
        float $insertionCost = 1.0,
        float $deletionCost = 1.0,
        float $replacementCost = 1.0,
        ?float $replacementAccentCost = 0.5,
        ?float $replacementCaseCost = 0.25
    ): float
    {
        if ($string1 === $string2) {
            return 0;
        }

        $length1 = mb_strlen($string1, 'UTF-8');
        $length2 = mb_strlen($string2, 'UTF-8');
        if ($length1 < $length2) {
            return self::levenshteinUnicode(
                $string2,
                $string1,
                $insertionCost,
                $deletionCost,
                $replacementCost,
                $replacementAccentCost,
                $replacementCaseCost
            );
        }
        if ($length1 === 0) {
            return (float) $length2;
        }

        $previousRow = range(0.0, $length2);
        for ($i = 0; $i < $length1; $i++) {
            $currentRow = [];
            $currentRow[0] = $i + 1.0;
            $char1 = mb_substr($string1, $i, 1, 'UTF-8');
            for ($j = 0; $j < $length2; $j++) {
                $char2 = mb_substr($string2, $j, 1, 'UTF-8');

                if ($char1 === $char2) {
                    $cost = 0;
                } elseif ($replacementCaseCost !== null && self::lower($char1) === self::lower($char2)) {
                    $cost = $replacementCaseCost;
                } elseif ($replacementAccentCost !== null && self::removeDiacritics($char1) === self::removeDiacritics($char2)) {
                    $cost = $replacementAccentCost;
                } elseif ($replacementCaseCost !== null && $replacementAccentCost !== null && self::removeDiacriticsAndLower($char1) === self::removeDiacriticsAndLower($char2)) {
                    $cost = $replacementCaseCost + $replacementAccentCost;
                } else {
                    $cost = $replacementCost;
                }
                $replacement = $previousRow[$j] + $cost;
                $insertions = $previousRow[$j + 1] + $insertionCost;
                $deletions = $currentRow[$j] + $deletionCost;

                $currentRow[] = min($replacement, $insertions, $deletions);
            }
            $previousRow = $currentRow;
        }

        return $previousRow[$length2];
    }

    public static function removeDiacritics(string $string): string
    {
        static $transliterator;
        if ($transliterator === null) {
            $transliterator = Transliterator::createFromIds([
                Transliterator::DECOMPOSE,
                [Transliterator::REMOVE, UnicodeCharacterCategory::NONSPACING_MARK],
                Transliterator::COMPOSE,
            ]);
        }

        return $transliterator->transliterate($string);
    }

    private static function removeDiacriticsAndLower(string $string): string
    {
        static $transliterator;
        if ($transliterator === null) {
            $transliterator = Transliterator::createFromIds([
                Transliterator::DECOMPOSE,
                [Transliterator::REMOVE, UnicodeCharacterCategory::NONSPACING_MARK],
                Transliterator::COMPOSE,
                Transliterator::LOWER_CASE,
            ]);
        }

        return $transliterator->transliterate($string);
    }

    // proxy -----------------------------------------------------------------------------------------------------------

    public static function checkEncoding(string $string): bool
    {
        return $string === self::fixEncoding($string);
    }

    public static function fixEncoding(string $string): string
    {
        return Strings::fixEncoding($string);
    }

    public static function chr(int $code): string
    {
        return Strings::chr($code);
    }

    public static function startsWith(string $string, string $find): bool
    {
        return strncmp($string, $find, strlen($find)) === 0;
    }

    public static function endsWith(string $string, string $find): bool
    {
        return strlen($find) === 0 || substr($string, -strlen($find)) === $find;
    }

    public static function contains(string $string, string $find): bool
    {
        return strpos($string, $find) !== false;
    }

    public static function substring(string $string, int $start, ?int $length = null): string
    {
        return Strings::substring($string, $start, $length);
    }

    public static function normalize(string $string): string
    {
        return Strings::normalize($string);
    }

    public static function normalizeNewLines(string $string): string
    {
        return str_replace(["\r\n", "\r"], "\n", $string);
    }

    public static function toAscii(string $string): string
    {
        return Strings::toAscii($string);
    }

    public static function webalize(string $string, ?string $chars = null, bool $lower = true): string
    {
        return Strings::webalize($string, $chars, $lower);
    }

    public static function truncate(string $string, int $maxLength, string $append = "\u{2026}"): string
    {
        return Strings::truncate($string, $maxLength, $append);
    }

    public static function indent(string $string, int $level = 1, string $chars = "\t"): string
    {
        return Strings::indent($string, $level, $chars);
    }

    public static function lower(string $string): string
    {
        return mb_strtolower($string, 'UTF-8');
    }

    public static function firstLower(string $string): string
    {
        return self::lower(self::substring($string, 0, 1)) . self::substring($string, 1);
    }

    public static function upper(string $string): string
    {
        return mb_strtoupper($string, 'UTF-8');
    }

    public static function firstUpper(string $string): string
    {
        return self::upper(self::substring($string, 0, 1)) . self::substring($string, 1);
    }

    public static function capitalize(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    public static function before(string $string, string $find, int $nth = 1): ?string
    {
        return Strings::before($string, $find, $nth);
    }

    public static function after(string $string, string $find, int $nth = 1): ?string
    {
        return Strings::after($string, $find, $nth);
    }

    public static function length(string $string): int
    {
        return Strings::length($string);
    }

    public static function trim(string $string, string $chars = Strings::TRIM_CHARACTERS): string
    {
        return Strings::trim($string, $chars);
    }

    public static function padRight(string $string, int $length, string $pad = ' '): string
    {
        return Strings::padRight($string, $length, $pad);
    }

    public static function padLeft(string $string, int $length, string $pad = ' '): string
    {
        return Strings::padLeft($string, $length, $pad);
    }

    /**
     * @param string $string
     * @param string $pattern
     * @param int $flags
     * @return string[]
     */
    public static function split(string $string, string $pattern, int $flags = 0): array
    {
        return Strings::split($string, $pattern, $flags);
    }

    /**
     * @param string $string
     * @param string $pattern
     * @param int $flags
     * @param int $offset
     * @return string[]|null
     */
    public static function match(string $string, string $pattern, int $flags = 0, int $offset = 0): ?array
    {
        return Strings::match($string, $pattern, $flags, $offset);
    }

    /**
     * @param string $string
     * @param string $pattern
     * @param int $flags
     * @param int $offset
     * @return string[]
     */
    public static function matchAll(string $string, string $pattern, int $flags = 0, int $offset = 0): array
    {
        return Strings::matchAll($string, $pattern, $flags, $offset);
    }

    /**
     * @param string $string
     * @param string|string[] $pattern
     * @param string|callable|null $replacement
     * @param int $limit
     * @return string
     */
    public static function replace(string $string, $pattern, $replacement = null, int $limit = -1): string
    {
        return Strings::replace($string, $pattern, $replacement, $limit);
    }

}
