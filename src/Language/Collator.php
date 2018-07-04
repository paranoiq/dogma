<?php declare(strict_types = 1);
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
use Dogma\Language\Locale\LocaleKeyword;
use Dogma\NotImplementedException;
use Dogma\Type;
use function is_string;

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
        if ($collation !== null) {
            throw new NotImplementedException('Named collations are not supported.');
        }
        $options = $locale->getCollationOptions();

        if ($collation === null && $options === []) {
            parent::__construct($locale->getValue());
        } else {
            // work around bug with parsing locale collation
            $safeLocale = $locale->removeCollation();

            parent::__construct($safeLocale->getValue());

            $this->configure($options);
        }
    }

    /**
     * @param \Dogma\Language\Locale\Locale|string $locale
     * @return self
     */
    public static function create($locale): self
    {
        return new self($locale);
    }

    /**
     * @param mixed[] $collationOptions
     */
    public function configure(array $collationOptions = []): void
    {
        /** @var \Dogma\Language\Locale\LocaleCollationOption $value */
        foreach ($collationOptions as $keyword => $value) {
            switch ($keyword) {
                case LocaleKeyword::COL_ALTERNATE:
                    $this->setAttribute(self::ALTERNATE_HANDLING, $value->getCollatorValue());
                    break;
                case LocaleKeyword::COL_BACKWARDS:
                    // cannot be configured directly
                    if ($value->getCollatorValue() === self::ON) {
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

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $str1
     * @param string $str2
     * @return int
     */
    public function compare($str1, $str2): int
    {
        if ($this->backwards) {
            return parent::compare($str2, $str1);
        } else {
            return parent::compare($str1, $str2);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param mixed[] $arr
     * @param int|null $sortFlag
     */
    public function asort(array &$arr, $sortFlag = null): void
    {
        parent::asort($arr, $sortFlag);
        if ($this->backwards) {
            $arr = Arr::reverse($arr);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param mixed[] $arr
     * @param int|null $sortFlag
     */
    public function sort(array &$arr, $sortFlag = null): void
    {
        parent::sort($arr, $sortFlag);
        if ($this->backwards) {
            $arr = Arr::reverse($arr);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param mixed[] $arr
     */
    public function sortWithSortKeys(array &$arr): void
    {
        parent::sortWithSortKeys($arr);
        if ($this->backwards) {
            $arr = Arr::reverse($arr);
        }
    }

    public function __invoke(string $str1, string $str2): int
    {
        return $this->compare($str1, $str2);
    }

}
