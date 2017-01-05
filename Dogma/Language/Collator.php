<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Language\Locale\Locale;
use Dogma\Language\Locale\LocaleCollation;
use Dogma\Language\Locale\LocaleKeyword;
use Dogma\Type;

class Collator extends \Collator
{
    /** @var bool */
    private $backwards = false;

    /**
     * @param \Dogma\Language\Locale\Locale|string $locale
     */
    public function __construct($locale)
    {
        Check::types($locale, [Locale::class, Type::STRING]);

        if (is_string($locale)) {
            $locale = Locale::get($locale);
        }

        $collation = $locale->getCollation();
        $options = $locale->getCollationOptions();

        if ($collation === null && $options === []) {
            parent::__construct($locale->getValue());
        } else {
            // work around bug with parsing locale collation
            $safeLocale = $locale->removeCollation();

            parent::__construct($safeLocale->getValue());

            $this->configure($collation, $options);
        }
    }

    public static function create($locale): self
    {
        return new self($locale);
    }

    public function configure(LocaleCollation $collation = null, array $collationOptions = [])
    {
        if ($collation !== null) {
            /// not supported?
        }
        /** @var \Dogma\Language\Locale\LocaleCollationOption $value */
        foreach ($collationOptions as $keyword => $value) {
            switch ($keyword) {
                case LocaleKeyword::COL_ALTERNATE:
                    $this->setAttribute(self::ALTERNATE_HANDLING, $value->getCollatorValue());
                    break;
                case LocaleKeyword::COL_BACKWARDS:
                    // cannot be configured directly
                    if ($value->getCollatorValue() === Collator::ON) {
                        $this->backwards = true;
                    }
                    break;
                case LocaleKeyword::COL_CASE_FIRST:
                    $this->setAttribute(self::CASE_FIRST, $value->getCollatorValue());
                    break;
                case LocaleKeyword::COL_HIRAGANA_QUATERNARY:
                    $this->setAttribute(self::HIRAGANA_QUATERNARY_MODE, $value->getCollatorValue());
                    break;
                case LocaleKeyword::COL_NORMALIZATION:
                    $this->setAttribute(self::NORMALIZATION_MODE, $value->getCollatorValue());
                    break;
                case LocaleKeyword::COL_NUMERIC:
                    $this->setAttribute(self::NUMERIC_COLLATION, $value->getCollatorValue());
                    break;
                case LocaleKeyword::COL_STRENGTH:
                    $this->setStrength($value->getCollatorValue());
                    break;
            }
        }
    }

    public function getLocaleObject(int $type = \Locale::ACTUAL_LOCALE): Locale
    {
        return Locale::get($this->getLocale($type));
    }

    public function compare($str1, $str2)
    {
        if ($this->backwards) {
            parent::compare($str2, $str1);
        } else {
            parent::compare($str1, $str2);
        }
    }

    public function asort(array &$arr, $sort_flag = null)
    {
        parent::asort($arr, $sort_flag);
        if ($this->backwards) {
            Arr::reverse($arr);
        }
    }

    public function sort(array &$arr, $sort_flag = null)
    {
        parent::sort($arr, $sort_flag);
        if ($this->backwards) {
            Arr::reverse($arr);
        }
    }

    public function sortWithSortKeys(array &$arr, $flags = null)
    {
        parent::sortWithSortKeys($arr, $flags);
        if ($this->backwards) {
            Arr::reverse($arr);
        }
    }

    public function __invoke(string $str1, string $str2): int
    {
        return $this->compare($str1, $str2);
    }

}
