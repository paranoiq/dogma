<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language;


/**
 * Dogma\Language\Collator is not a subclass of \Collator. Intentionaly!
 * Constructor of \Collator mutes a crucial error, when an unknown collation is passed.
 *
 * Lazy initialization
 */
class Collator extends \Nette\Object
{

    /** param values */
    const ON = true,
        OFF = false,
        AUTO = null;

    /** collation levels */
    const LETTER = 0,
        LETTER_CASE = 5, // 0, CASE_LEVEL = ON
        LETTER_ACCENT = 1,
        ACCENT_CASE = 2, // default
        PUNCTUATION = 3,
        IDENTICAL = 4;


    /** @var resource */
    protected $collator;

    /** @var string */
    protected $locale;

    /** @var int */
    protected $level;

    public function __construct(string $locale, int $collationLevel = self::ACCENT_CASE)
    {
        $this->locale = $locale;
        $this->level = $collationLevel;
    }

    private function init()
    {
        $this->collator = collator_create((string) $this->locale);
        if (collator_get_error_code($this->collator)) {
            throw new CollatorException('Collator: Invalid locale identificator!');
        }

        $this->setCollationLevel($this->level);
    }

    /**
     * Comparison callback. Returns (-1,0,1)
     */
    public function __invoke(string $str1, string $str2): int
    {
        return $this->compare($str1, $str2);
    }

    /**
     * Comapre two strings. Returns (-1,0,1)
     */
    public function compare(string $str1, string $str2): int
    {
        if (!$this->collator) {
            $this->init();
        }

        $result = collator_compare($this->collator, (string) $str1, (string) $str2);
        if ($result === false) {
            $this->throwError('Comparation');
        }
        return $result;
    }

    /**
     * Get locale code
     */
    public function getLocale(int $type = \Locale::ACTUAL_LOCALE): string
    {
        if (!$this->collator) {
            return $this->locale;
        }

        $result = collator_get_locale($this->collator, $type);
        if ($result === false) {
            $this->throwError('Getting locale');
        }
        return $result;
    }

    /**
     * Returns ISO language code
     */
    public function getLanguageCode(): string
    {
        return substr($this->getLocale(), 0, 2);
    }

    /**
     * Get collation level
     */
    public function getCollationLevel(): int
    {
        if (!$this->collator) {
            return $this->level;
        }

        $result = collator_get_strength($this->collator);
        if ($result === false) {
            $this->throwError('Getting collation level');
        }
        if ($result === self::LETTER && $this->getAttribute(\Collator::CASE_LEVEL) === \Collator::ON) {
            $result = self::LETTER_CASE;
        }
        return $result;
    }

    /**
     * Set collation level
     */
    public function setCollationLevel(int $level)
    {
        if (!$this->collator) {
            $this->init();
        }

        if ($level === self::LETTER_CASE) {
            $this->setAttribute(\Collator::CASE_LEVEL, \Collator::ON);
            $level = self::LETTER;
        }
        $result = collator_set_strength($this->collator, $strenght);
        if ($result === false) {
            $this->throwError('Setting collation level');
        }
    }

    /**
     * Get attribute value
     * @param string
     * @return mixed
     */
    public function getAttribute(string $name)
    {
        if (!$this->collator) {
            $this->init();
        }

        $result = collator_get_attribute($this->collator, $name);
        if ($result === false) {
            $this->throwError('Getting attribute');
        }
        return $result === \Collator::ON ? self::ON : ($result === \Collator::OFF ? self::OFF : ($result === \Collator::DEFAULT_VALUE ? self::AUTO : $result));
    }

    /**
     * Set attribute value
     * @param string
     * @param mixed
     */
    public function setAttribute(string $name, $value)
    {
        if (!$this->collator) {
            $this->init();
        }

        $value = $result === self::ON ? \Collator::ON : ($result === self::OFF ? \Collator::OFF : ($result === self::AUTO ? \Collator::DEFAULT_VALUE : $result));
        $result = collator_set_attribute($this->collator, $name, $value);
        if ($result === false) {
            $this->throwError('Setting attribute');
        }
    }

    private function throwError($action)
    {
        $message = ucfirst(strtolower(preg_replace(['/^U_/', '/_/'], [' ', ''], collator_get_error_message($this->collator))));
        throw new CollatorException(
            sprintf('Collator: %s failed with message: %s (%s).', $action, $message, collator_get_error_code($this->collator))
        );
    }

}
