<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;

use Collator as PhpCollator;
use Dogma\Arr;
use Dogma\Check;
use Dogma\InvalidArgumentException;
use Dogma\Language\Locale\Locale;
use Dogma\Language\Locale\LocaleCollationOption;
use Dogma\Language\Locale\LocaleKeyword;
use Dogma\NotImplementedException;
use Dogma\Type;
use Locale as PhpLocale;
use function is_string;

class Collator extends PhpCollator
{

    private bool $backwards = false;

    public function __construct(Locale|string $locale)
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

        if ($options === []) {
            parent::__construct($locale->getValue());
        } else {
            // work around bug with parsing locale collation
            $safeLocale = $locale->removeCollation();

            parent::__construct($safeLocale->getValue());

            $this->configure($options);
        }
    }

    public static function create(Locale|string $locale): self
    {
        return new self($locale);
    }

    /**
     * @param array<LocaleCollationOption> $collationOptions
     */
    public function configure(array $collationOptions = []): void
    {
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

    public function getLocaleObject(int $type = PhpLocale::ACTUAL_LOCALE): Locale
    {
        /** @var string|false $locale */
        $locale = $this->getLocale($type);
        if ($locale === false) {
            throw new InvalidArgumentException('Invalid locale type.');
        }

        return Locale::get($locale);
    }

    public function compare(string $string1, string $string2): int
    {
        if ($this->backwards) {
            $result = parent::compare($string2, $string1);
        } else {
            $result = parent::compare($string1, $string2);
        }
        if ($result === false) {
            throw new InvalidArgumentException('Incomparable strings given.');
        }

        return $result;
    }

    /**
     * @param array<mixed> $array
     */
    public function asort(array &$array, int $flags = self::SORT_REGULAR): bool
    {
        $result = parent::asort($array, $flags);

        if ($this->backwards) {
            $array = Arr::reverse($array);
        }

        return $result;
    }

    /**
     * @param array<mixed> $array
     */
    public function sort(array &$array, int $flags = self::SORT_REGULAR): bool
    {
        $result = parent::sort($array, $flags);

        if ($this->backwards) {
            $array = Arr::reverse($array);
        }

        return $result;
    }

    /**
     * @param array<mixed> $array
     */
    public function sortWithSortKeys(array &$array): bool
    {
        $result = parent::sortWithSortKeys($array);

        if ($this->backwards) {
            $array = Arr::reverse($array);
        }

        return $result;
    }

    public function __invoke(string $str1, string $str2): int
    {
        return $this->compare($str1, $str2);
    }

}
