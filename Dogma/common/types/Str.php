<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Str extends \Nette\Utils\Strings
{

    /**
     * Test equality with another string
     * @param string $first
     * @param string|\Collator|null
     * @return bool
     */
    public function equals(string $first, string $second, $collation = CaseComparison::CASE_SENSITIVE): bool
    {
        return self::compare($first, $second, $collation) === 0;
    }

    /**
     * Compare to another string
     * @param string $first
     * @param string|\Collator
     * @return bool
     */
    public static function compare($first, $second, $collation = CaseComparison::CASE_SENSITIVE): bool
    {
        if ($collation === CaseComparison::CASE_SENSITIVE) {
            return strcmp($first, $second);
        } elseif ($collation === CaseComparison::CASE_INSENSITIVE) {
            return strcasecmp($first, $second);
        } elseif (is_string($collation)) {
            $collation = new Language\Collator($collation);
        }
        return $collation->compare($first, $second);
    }
    
}
