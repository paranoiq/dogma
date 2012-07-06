<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


/*
//z condition:
startsWith
endsWith
contains
match

lower
upper
upperFirst
upperWords
setCaseByList

trim
trimLeft
trimRight

replace
removeDiacritics

pad

word count?

*/

/**
 * Basic object for a UTF-8 string.
 * Strings are always kept normalized.
 */
class String implements \ArrayAccess {
    
    /** @var string */
    protected $string = '';
    
    
    
    /**
     * @param string
     */
    public function __construct($string) {
        $this->append($string);
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->string;
    }
    
    
    /**
     * Get string legnth
     * @return int     
     */
    public function length() {
        return mb_strlen($this->string);
    }
    
    
    /**
     * Append to the end of string
     * @param string|String
     * @return String
     */
    public function append($string) {
        $this->string .= static::normalize($string);
        return $this;
    }
    
    
    /**
     * Prepend to the beginning of string
     * @param string|String
     * @return String
     */
    public function prepend($string) {
        $this->string = static::normalize($string) . $this->string;
        return $this;
    }
    
    
    ///
    
    
    /**
     * Test equality with another string
     * @param string
     * @param string|Collator
     */
    public function equalsTo($string, $collator = NULL) {
        return $this->compareTo($string, $collator) === 0;
    }
    
    
    /**
     * Compare to another string
     * @param string
     * @param string|Collator
     */
    public function compareTo($string, $collator = NULL) {
        if ($collator = NULL) strcmp($this->string, $string);
        
        if (!$collator instanceof Language\Collator) $collator = new Language\Collator($collator);
        return $collator->compare($this->string, $string);
    }
    
    
    public function contains($string, $collation = NULL) {
        ///
    }
    
    
    ///
    private function testWithCollator($value) {
        switch ($this->operator) {
        
        case self::EQUAL:
            return $this->decide($this->collator->compare($value, $this->value) === 0);
            
        case self::LOWER:
            return $this->decide($this->collator->compare($value, $this->value) === -1);
            
        case self::GREATER:
            return $this->decide($this->collator->compare($value, $this->value) === 1);
            
        case self::RANGE:
            return $this->decide(
                $this->collator->compare($value, $this->value[0]) !== -1 && 
                $this->collator->compare($value, $this->value[1]) !== 1);
            
        case self::IN:
            return $this->decide($this->value->contains($value, $this->collator));
            
        case self::STARTS:
            $value = \Normalizer::normalize($value);
            $pattern = \Normalizer::normalize($this->value);
            return $this->decide($this->collator->compare(mb_substr($value, 0, mb_strlen($pattern)), $pattern) === 0);
            
        case self::CONTAINS:
            $value = \Normalizer::normalize($value);
            $pattern = \Normalizer::normalize($this->value);
            
            /// speed up
            for ($n = 0; $n < mb_strlen($value) - mb_strlen($pattern); $n++) {
                if ($this->collator->compare(mb_substr($value, $n, mb_strlen($pattern)), $pattern) === 0)
                    return $this->decide(TRUE);
            }
            return $this->decide(FALSE);
            
        case self::LIKE:
            ///
            break;
            
        case self::MATCH:
            return $this->decide($this->value->match($value));
        }
    }
    
    
    /**
     * Normalize string
     * @return string     
     */
    public static function normalize($string) {
        if ($string instanceof String) {
            return $string->string;
            
        } elseif (is_string($string)) {
            return \Normalizer::normalize($string);
        
        } elseif (is_object($string) && has_method($string, '__toString')) {
            return \Normalizer::normalize($string->__toString());
            
        } else {
            throw new \LogicException('String: Given value is not a string.');
        }
    }
    
    
    // Array Access ----------------------------------------------------------------------------------------------------
    
    
    /**#@+ ArrayAccess interface */
    public function offsetSet($key, $value) {
        if ($key === NULL) {
            $this->append($value);
        } else {
            $this->string = mb_substr($this->string, 0, $key) . static::normalize($value) . mb_substr($this->string, ++$key);
        }
    }
    
    public function offsetGet($key) {
        return mb_substr($this->string, $key, 1);
    }
    
    public function offsetExists($key) {
        return is_string(mb_substr($this->string, $key, 1));
    }
    
    public function offsetUnset($key) {
        throw new \LogicException('String: Cannot unset a string offset.');
    }
    /**#@-*/
    
    
}
