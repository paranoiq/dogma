<?php

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
