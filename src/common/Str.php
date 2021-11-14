<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.Classes.ClassMemberSpacing.IncorrectCountOfBlankLinesBetweenMembers

namespace Dogma;

use Collator as PhpCollator;
use Dogma\Language\Collator;
use Dogma\Language\Encoding;
use Dogma\Language\Locale\Locale;
use Dogma\Language\Transliterator;
use Dogma\Language\UnicodeCharacterCategory;
use Error;
use Nette\Utils\Strings;
use UConverter;
use const MB_CASE_TITLE;
use function array_keys;
use function array_pop;
use function array_values;
use function class_exists;
use function error_clear_last;
use function error_get_last;
use function function_exists;
use function iconv;
use function implode;
use function is_string;
use function mb_convert_case;
use function mb_convert_encoding;
use function mb_strlen;
use function mb_strtolower;
use function mb_strtoupper;
use function mb_substr;
use function min;
use function preg_replace;
use function range;
use function str_replace;
use function strcasecmp;
use function strcmp;
use function strlen;
use function strncmp;
use function strpos;
use function strrpos;
use function strtolower;
use function substr;
use function utf8_decode;
use function utf8_encode;

/**
 * UTF-8 strings manipulation
 */
class Str
{
    use StaticClassMixin;

    // proxy -----------------------------------------------------------------------------------------------------------

    public static function checkEncoding(string $string): bool
    {
        return $string === Strings::fixEncoding($string);
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
        return $find === '' || substr($string, -strlen($find)) === $find;
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

    public static function before(string $string, string $find, int $nth = 1): ?string
    {
        return Strings::before($string, $find, $nth);
    }

    public static function after(string $string, string $find, int $nth = 1): ?string
    {
        return Strings::after($string, $find, $nth);
    }

    // substrings etc --------------------------------------------------------------------------------------------------

    public static function between(string $string, string $from, string $to): ?string
    {
        $after = self::after($string, $from);
        if ($after === null) {
            return null;
        }

        return self::before($after, $to);
    }

    /**
     * Similar to before(), but always returns start or the entire string
     *
     * @return string
     */
    public static function toFirst(string $string, string $search): string
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return $string;
        }

        return substr($string, 0, $pos);
    }

    /**
     * Similar to after(), but always returns end or entire string
     *
     * @return string
     */
    public static function fromFirst(string $string, string $search): string
    {
        $pos = strpos($string, $search);
        if ($pos === false) {
            return '';
        }

        return substr($string, $pos + 1);
    }

    /**
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
     * @return string[]
     */
    public static function splitByLast(string $string, string $search): array
    {
        $pos = strrpos($string, $search);
        if ($pos === false) {
            return [$string, ''];
        }

        return [substr($string, 0, $pos), substr($string, $pos + 1)];
    }

    public static function getLineAt(string $string, int $position, string $separator = "\n"): string
    {
        $before = substr($string, 0, $position);
        $lineStart = strrpos($before, $separator);
        if ($lineStart === false) {
            $lineStart = 0;
        }
        $lineEnd = strpos($string, $separator, $position);
        if ($lineEnd === false) {
            $lineEnd = strlen($string) - 1;
        }

        return substr($string, $lineStart + 1, $lineEnd - $lineStart - 1);
    }

    /**
     * Implode with optional different separator for last item in the list ("A, B, C and D")
     * @param string[] $items
     */
    public static function join(array $items, string $separator = '', ?string $lastSeparator = null): string
    {
        if (count($items) === 0) {
            return '';
        } elseif (count($items) === 1) {
            return (string) array_pop($items);
        } elseif ($lastSeparator === null) {
            return implode($separator, $items);
        } else {
            $last = array_pop($items);

            return implode($separator, $items) . $lastSeparator . $last;
        }
    }

    /**
     * @see Re::count()
     * @return int
     */
    public static function count(string $string, string $substring): int
    {
        return (strlen($string) - strlen(str_replace($substring, '', $string))) / strlen($substring);
    }

    /**
     * @deprecated use Str::count() instead
     * @return int
     */
    public static function substringCount(string $string, string $substring): int
    {
        return (strlen($string) - strlen(str_replace($substring, '', $string))) / strlen($substring);
    }

    /**
     * @param string[] $replacements
     */
    public static function replaceKeys(string $string, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $string);
    }

    // misc ------------------------------------------------------------------------------------------------------------

    public static function underscore(string $string): string
    {
        return strtolower(preg_replace(
            '/([A-Z]+)([A-Z])/',
            '\1_\2',
            preg_replace('/([a-z\d])([A-Z])/', '\1_\2', $string)
        ));
    }

    public static function trimLinesRight(string $string): string
    {
        return Re::replace($string, "/[\t ]+\n/", "\n");
    }

    // comparison ------------------------------------------------------------------------------------------------------

    /**
     * @param int|string|Collator|Locale $collation
     * @return bool
     */
    public static function equals(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): bool
    {
        return self::compare($first, $second, $collation) === 0;
    }

    /**
     * @param int|string|Collator|Locale $collation
     * @return int
     */
    public static function compare(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): int
    {
        if ($collation === CaseComparison::CASE_SENSITIVE) {
            return strcmp($first, $second);
        } elseif ($collation === CaseComparison::CASE_INSENSITIVE) {
            return strcasecmp($first, $second);
        } elseif (is_string($collation) || $collation instanceof Locale) {
            $collation = new Collator($collation);
        } elseif (!$collation instanceof PhpCollator) {
            throw new InvalidValueException($collation, [Type::STRING, PhpCollator::class, Locale::class]);
        }

        return $collation->compare($first, $second);
    }

    /**
     * Locates a "tag" surrounded by given markers which may be escaped. Returns start and length pair.
     *
     * Eg. called with ("foo {{no-tag}} {tag}}body} bar", '{', '}', '{', '}) will return [15, 12] for the "{tag{{body}"
     *
     * @return int[]|null[] ($start, $length)
     */
    public static function findTag(
        string $string,
        string $start,
        string $end,
        ?string $startEscape = null,
        ?string $endEscape = null,
        int $offset = 0
    ): ?array
    {
        $seDouble = $start === $startEscape;
        $seLength = $startEscape ? strlen($startEscape) : 0;
        do {
            $i = strpos($string, $start, $offset);
            if ($i === false) {
                return [null, null];
            }
            if ($startEscape === null) {
                break;
            }
            if ($seDouble) {
                $next = substr($string, $i + 1, $seLength);
                if ($next !== $startEscape) {
                    break;
                }
                $offset = $i + $seLength + 1;
            } else {
                $prev = substr($string, $i - $seLength, $seLength);
                if ($prev !== $startEscape) {
                    break;
                }
                $offset++;
            }
        } while (true);
        $offset = $i + strlen($start) + 1;

        $eeDouble = $end === $endEscape;
        $eeLength = $endEscape ? strlen($startEscape) : 0;
        do {
            $j = strpos($string, $end, $offset);
            if ($j === false) {
                return [null, null];
            }
            if ($endEscape === null) {
                break;
            }
            if ($eeDouble) {
                $next = substr($string, $j + 1, $eeLength);
                if ($next !== $endEscape) {
                    break;
                }
                $offset = $j + $eeLength + 1;
            } else {
                $prev = substr($string, $j - $eeLength, $eeLength);
                if ($prev !== $endEscape) {
                    break;
                }
                $offset++;
            }
        } while (true);

        return [$i, $j - $i + 1];
    }

    /**
     * Levenshtein distance for UTF-8 with additional weights for accent and case differences.
     * Expects input strings to be normalized UTF-8.
     *
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

    // character manipulation ------------------------------------------------------------------------------------------

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

    public static function convertEncoding(string $string, string $from, string $to): string
    {
        if (function_exists('mb_convert_encoding')) {
            try {
                error_clear_last();
                $result = mb_convert_encoding($string, $to, $from);
                if (error_get_last() !== null) {
                    throw new ErrorException('Cannot convert encoding', error_get_last());
                }

                return $result;
            } catch (Error $e) {
                throw new ErrorException('Cannot convert encoding', null, $e);
            }
        } elseif (function_exists('iconv')) {
            try {
                error_clear_last();
                $result = iconv($from, $to, $string);
                if ($result === false) {
                    throw new ErrorException('Cannot convert encoding', error_get_last());
                }

                return $result;
            } catch (Error $e) {
                throw new ErrorException('Cannot convert encoding', null, $e);
            }
        } elseif (class_exists(UConverter::class)) {
            // from intl extension
            $converter = new UConverter($from, $to);

            error_clear_last();
            $result = $converter->convert($string);
            if ($result === false) {
                throw new ErrorException('Cannot convert encoding.', error_get_last());
            }

            return $result;
        } elseif (function_exists('recode_string')) {
            $request = $from . '..' . $to;
            try {
                error_clear_last();
                $result = recode_string($request, $string);
                if ($result === false) {
                    throw new ErrorException('Cannot convert encoding', error_get_last());
                }

                return $result;
            } catch (Error $e) {
                throw new ErrorException('Cannot convert encoding', null, $e);
            }
        } elseif ($from === Encoding::ISO_8859_1 && $to === Encoding::UTF_8 && function_exists('utf8_encode')) {
            return utf8_encode($string);
        } elseif ($from === Encoding::UTF_8 && $to === Encoding::ISO_8859_1 && function_exists('utf8_decode')) {
            return utf8_decode($string);
        } else {
            throw new ShouldNotHappenException('No extension for converting encodings installed.');
        }
    }

    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @deprecated use Re::split() instead
     * @return string[]
     */
    public static function split(string $string, string $pattern, int $flags = 0): array
    {
        return Re::split($string, $pattern, $flags);
    }

    /**
     * @deprecated use Re::match() instead
     * @return string[]|null
     */
    public static function match(string $string, string $pattern, int $flags = 0, int $offset = 0): ?array
    {
        return Re::match($string, $pattern, $flags, $offset);
    }

    /**
     * @deprecated use Re::matchAll() instead
     * @return string[][]
     */
    public static function matchAll(string $string, string $pattern, int $flags = 0, int $offset = 0): array
    {
        return Re::matchAll($string, $pattern, $flags, $offset);
    }

    /**
     * @deprecated use Re::replace() instead
     * @param string|string[] $pattern
     * @param string|callable|null $replacement
     * @return string
     */
    public static function replace(string $string, $pattern, $replacement = null, int $limit = -1): string
    {
        return Re::replace($string, $pattern, $replacement, $limit);
    }

}
