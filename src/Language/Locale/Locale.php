<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language\Locale;

use Dogma\Arr;
use Dogma\Check;
use Dogma\Country\Country;
use Dogma\Language\Collator;
use Dogma\Language\Language;
use Dogma\Language\Script;
use Dogma\Money\Currency;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use function array_filter;
use function array_values;
use function implode;
use function is_string;
use function preg_match;
use function reset;
use function sprintf;

class Locale
{
    use StrictBehaviorMixin;

    /** @var self[] */
    private static $instances = [];

    /** @var string */
    private $value;

    /** @var string[]|string[][] */
    private $components;

    /**
     * @param string $value
     * @param string[]|string[][] $components
     */
    final private function __construct(string $value, array $components)
    {
        $this->value = $value;
        $this->components = $components;
    }

    public static function get(string $value): self
    {
        $value = \Locale::canonicalize($value);

        if (isset(self::$instances[$value])) {
            return self::$instances[$value];
        } else {
            $components = \Locale::parseLocale($value);
            $keywords = \Locale::getKeywords($value);
            if ($keywords) {
                $components['keywords'] = $keywords;
            }
            $instance = new self($value, $components);
            self::$instances[$value] = $instance;
            return $instance;
        }
    }

    public static function getDefault(): self
    {
        return self::get(\Locale::getDefault());
    }

    /**
     * @param \Dogma\Language\Language $language
     * @param \Dogma\Country\Country|null $country
     * @param \Dogma\Language\Script|null $script
     * @param string[] $variants
     * @param string[] $private
     * @param string[] $keywords
     * @return self
     */
    public static function create(
        Language $language,
        ?Country $country = null,
        ?Script $script = null,
        array $variants = [],
        array $private = [],
        array $keywords = []
    ): self
    {
        $components = [
            'language' => $language->getValue(),
            'region' => $country ? $country->getValue() : null,
            'script' => $script ? $script->getValue() : null,
        ];
        foreach (array_values($variants) as $n => $value) {
            $components['variant' . $n] = $value;
        }
        foreach (array_values($private) as $n => $value) {
            $components['private' . $n] = $value;
        }

        $value = \Locale::composeLocale(array_filter($components));
        if ($keywords) {
            $value .= '@' . implode(';', Arr::mapPairs($keywords, function (string $key, string $value) {
                return $key . '=' . $value;
            }));
        }
        $value = \Locale::canonicalize($value);

        if (isset(self::$instances[$value])) {
            return self::$instances[$value];
        } else {
            $components['keywords'] = $keywords;
            $instance = new self($value, $components);
            self::$instances[$value] = $instance;
            return $instance;
        }
    }

    public function getCollator(): Collator
    {
        return new Collator($this);
    }

    /**
     * @param string|\Dogma\Language\Locale\Locale $locale
     * @return bool
     */
    public function matches($locale): bool
    {
        Check::types($locale, [Type::STRING, self::class]);

        if (is_string($locale)) {
            $locale = self::get($locale);
        }

        return \Locale::filterMatches($this->value, $locale->getValue(), false);
    }

    /**
     * @param \Dogma\Language\Locale\Locale[]|string[] $locales
     * @param \Dogma\Language\Locale\Locale|string $default
     * @return self|null
     */
    public function findBestMatch(array $locales, $default = null): ?self
    {
        Check::types($default, [Type::STRING, self::class, Type::NULL]);

        if ($default === null) {
            // work around bug when lookup does not work at all without default value
            $default = reset($locales);
        }
        if (is_string($default)) {
            $default = self::get($default);
        }

        foreach ($locales as $i => $locale) {
            Check::types($locale, [Type::STRING, self::class]);
            if (is_string($locale)) {
                $locales[$i] = \Locale::canonicalize($locale);
            } else {
                /** @var \Dogma\Language\Locale\Locale $locale */
                $locale[$i] = $locale->getValue();
            }
        }

        $match = \Locale::lookup($locales, $this->value, false, $default->getValue());

        return $match ? self::get($match) : null;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLanguage(): ?Language
    {
        if (empty($this->components['language'])) {
            return null;
        }
        return Language::get($this->components['language']);
    }

    public function getScript(): ?Script
    {
        if (empty($this->components['script'])) {
            return null;
        }
        return Script::get($this->components['script']);
    }

    public function getCountry(): ?Country
    {
        if (empty($this->components['region'])) {
            return null;
        }
        return Country::get($this->components['region']);
    }

    /**
     * @return string[]
     */
    public function getVariants(): array
    {
        return \Locale::getAllVariants($this->value);
    }

    public function getVariant(int $n): ?string
    {
        $key = 'variant' . $n;
        if (empty($this->components[$key])) {
            return null;
        }
        return $this->components[$key];
    }

    public function hasVariant(string $variant): bool
    {
        return Arr::contains($this->getVariants(), $variant);
    }

    /**
     * @return string[]
     */
    public function getPrivates(): array
    {
        $privates = [];
        foreach ($this->components as $key => $component) {
            if (preg_match('/^private\\d+$/', $key)) {
                // work around bug, when last private variant is returned with keywords
                $privates[] = Str::toFirst($component, '@');
            }
        }
        return $privates;
    }

    public function getPrivate(int $n): ?string
    {
        $key = 'private' . $n;
        if (empty($this->components[$key])) {
            return null;
        }
        // work around bug, when last private variant is returned with keywords
        return Str::toFirst($this->components[$key], '@');
    }

    /**
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->components['keywords'] ?? [];
    }

    public function getKeyword(string $keyword): ?string
    {
        return $this->components['keywords'][$keyword] ?? null;
    }

    public function getCurrency(): ?Currency
    {
        $value = $this->getKeyword(LocaleKeyword::CURRENCY);

        return $value ? Currency::get($value) : null;
    }

    public function getNumbers(): ?LocaleNumbers
    {
        $value = $this->getKeyword(LocaleKeyword::NUMBERS);

        return $value ? LocaleNumbers::get($value) : null;
    }

    public function getCalendar(): ?LocaleCalendar
    {
        $value = $this->getKeyword(LocaleKeyword::CALENDAR);

        return $value ? LocaleCalendar::get($value) : null;
    }

    public function getCollation(): ?LocaleCollation
    {
        $value = $this->getKeyword(LocaleKeyword::COLLATION);

        return $value ? LocaleCollation::get($value) : null;
    }

    /**
     * @return string[]
     */
    public function getCollationOptions(): array
    {
        $options = [];
        foreach (LocaleKeyword::getCollationOptions() as $keyword => $class) {
            $value = $this->getKeyword($keyword);
            if ($value !== null) {
                /** @var \Dogma\Language\Locale\LocaleCollationOption $class */
                $options[$keyword] = $class::get($value);
            }
        }
        return $options;
    }

    public function removeCollation(): self
    {
        $keywords = Arr::diffKeys($this->getKeywords(), LocaleKeyword::getCollationOptions());
        unset($keywords[LocaleKeyword::COLLATION]);

        return self::create(
            $this->getLanguage(),
            $this->getCountry(),
            $this->getScript(),
            $this->getVariants(),
            $this->getPrivates(),
            $keywords
        );
    }

    public static function getValueRegexp(): string
    {
        static $regexp;

        if (!$regexp) {
            // language_Script_COUNTRY_VARIANT_VARIANT...@keyword=value;keyword=value...
            $regexp = sprintf(
                '%s(?:_(?:%s))?(?:_(?:%s))?(?:_(?:%s))*(?:@(?:%s)=[a-zA-Z0-9-](?:;(?:%s)=[a-zA-Z0-9-])*)?',
                Language::getValueRegexp(),
                Script::getValueRegexp(),
                Country::getValueRegexp(),
                LocaleVariant::getValueRegexp(),
                LocaleKeyword::getValueRegexp(),
                LocaleKeyword::getValueRegexp()
            );
        }
        return $regexp;
    }

}
