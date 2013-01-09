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
 * Collator exception
 */
class CollatorException extends \Exception { }


/**
 * Dogma\Language\Collator is not a subclass of \Collator. Intentionaly!
 * Constructor of \Collator mutes a crucial error, when an unknown collation is passed.
 *
 * Lazy initialization
 */
class Collator extends \Nette\Object {

    /** param values */
    const ON = TRUE,
        OFF = FALSE,
        AUTO = NULL;

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


    /**
     * @param string
     * @param int
     */
    public function __construct($locale, $collationLevel = self::ACCENT_CASE) {
        $this->locale = $locale;
        $this->level = $collationLevel;
    }


    private function init() {
        $this->collator = collator_create((string) $this->locale);
        if (collator_get_error_code($this->collator))
            throw new CollatorException("Collator: Invalid locale identificator!");

        $this->setCollationLevel($this->level);
    }


    /**
     * Comparison callback
     * @param string
     * @param string
     * @return int (-1,0,1)
     */
    public function __invoke($str1, $str2) {
        return $this->compare($str1, $str2);
    }


    /**
     * Comapre two strings
     * @param string
     * @param string
     * @return int (-1,0,1)
     */
    public function compare($str1, $str2) {
        if (!$this->collator) $this->init();

        $result = collator_compare($this->collator, (string) $str1, (string) $str2);
        if ($result === FALSE) $this->throwError('Comparation');
        return $result;
    }


    /**
     * Get locale code
     * @param int
     * @return string
     */
    public function getLocale($type = \Locale::ACTUAL_LOCALE) {
        if (!$this->collator) return $this->locale;

        $result = collator_get_locale($this->collator, $type);
        if ($result === FALSE) $this->throwError('Getting locale');
        return $result;
    }


    /**
     * Returns ISO language code
     * @return string
     */
    public function getLanguageCode() {
        return substr($this->getLocale(), 0, 2);
    }


    /**
     * Get collation level
     * @return int
     */
    public function getCollationLevel() {
        if (!$this->collator) return $this->level;

        $result = collator_get_strength($this->collator);
        if ($result === FALSE) $this->throwError('Getting collation level');
        if ($result === self::LETTER && $this->getAttribute(\Collator::CASE_LEVEL) === \Collator::ON) {
            $result = self::LETTER_CASE;
        }
        return $result;
    }


    /**
     * Set collation level
     * @param int
     * @return Collator
     */
    public function setCollationLevel($level) {
        if (!$this->collator) $this->init();

        if ($level === self::LETTER_CASE) {
            $this->setAttribute(\Collator::CASE_LEVEL, \Collator::ON);
            $level = self::LETTER;
        }
        $result = collator_set_strength($this->collator, $strenght);
        if ($result === FALSE) $this->throwError('Setting collation level');

        return $this;
    }


    /**
     * Get attribute value
     * @param string
     * @return mixed
     */
    public function getAttribute($name) {
        if (!$this->collator) $this->init();

        $result = collator_get_attribute($this->collator, $name);
        if ($result === FALSE) $this->throwError('Getting attribute');
        return $result === \Collator::ON ? self::ON : ($result === \Collator::OFF ? self::OFF : ($result === \Collator::DEFAULT_VALUE ? self::AUTO : $result));
    }


    /**
     * Set attribute value
     * @param string
     * @param mixed
     * @return Collator
     */
    public function setAttribute($name, $value) {
        if (!$this->collator) $this->init();

        $value = $result === self::ON ? \Collator::ON : ($result === self::OFF ? \Collator::OFF : ($result === self::AUTO ? \Collator::DEFAULT_VALUE : $result));
        $result = collator_set_attribute($this->collator, $name, $value);
        if ($result === FALSE) $this->throwError('Setting attribute');

        return $this;
    }


    private function throwError($action) {
        throw new CollatorException("Collator: $action failed with message: "
            . ucFirst(strToLower(preg_replace(array('/^U_/', '/_/'), array(' ', ''), collator_get_error_message($this->collator))))
            . ' (' . collator_get_error_code($this->collator) . ').');
    }

}
