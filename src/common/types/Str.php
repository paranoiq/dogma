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

/**
 * UTF-8 strings manipulation
 */
class Str extends \Nette\Utils\Strings
{

    /**
     * Test equality with another string
     * @param string $first
     * @param string $second
     * @param string|\Collator|\Dogma\Language\Locale\Locale $collation
     * @return bool
     */
    public function equals(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): bool
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
            throw new \Dogma\InvalidValueException($collation, [Type::STRING, \Collator::class, Locale::class]);
        }
        return $collation->compare($first, $second);
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

}
