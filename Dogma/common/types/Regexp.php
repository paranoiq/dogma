<?php

namespace Dogma;

use Nette\Diagnostics\Debugger;
use Nette\Callback;
use Nette\Utils\RegexpException;

//class RegexpException extends \Nette\RegexpException {}


/**
 * Regular expression object
 * @see http://phpfashion.com/zradne-regularni-vyrazy-v-php
 * @author David Grudl
 * @author Vlasta Neubauer
 */
class Regexp extends \Nette\Object {
    
    
    /** @var string */
    private $pattern;
    
    
    /**
     * @param string
     */    
    public function __construct($pattern) {
        //if ($pattern instanceof \MongoRegex) {
        //    $this->pattern = '/' . str_replace('/', '\/', $pattern->regex) . '/' . $pattern->flags;
        //} else {
            $this->pattern = $pattern;
        //}
    }


    /**
     * @return string
     */
    public function __toString() {
        return $this->pattern;
    }
    
    
    /**
     * Splits string by a regular expression.
     * @param  string
     * @param  int
     * @return array
     */
    public function split($subject, $flags = 0) {
        Debugger::tryError();
        $res = preg_split($this->pattern, $subject, -1, $flags | PREG_SPLIT_DELIM_CAPTURE);
        self::catchPregError($this->pattern);
        return $res;
    }
    
    
    /**
     * Performs a regular expression match.
     * @param  string
     * @param  int
     * @param  int
     * @return mixed
     */
    public function match($subject, $flags = 0, $offset = 0) {
        Debugger::tryError();
        $res = preg_match($this->pattern, $subject, $m, $flags, $offset);
        self::catchPregError($this->pattern);
        if ($res) {
            return $m;
        }
    }
    
    
    /**
     * Performs a global regular expression match.
     * @param  string
     * @param  int  (PREG_SET_ORDER is default)
     * @param  int
     * @return array
     */
    public function matchAll($subject, $flags = 0, $offset = 0) {
        Debugger::tryError();
        $res = preg_match_all($this->pattern, $subject, $m, ($flags & PREG_PATTERN_ORDER) ? $flags : ($flags | PREG_SET_ORDER), $offset);
        self::catchPregError($this->pattern);
        return $m;
    }
    
    
    /**
     * Perform a regular expression search and replace.
     * @param  string
     * @param  string|callback
     * @param  int
     * @return string
     */
    public function replace($subject, $replacement = NULL, $limit = -1) {
        Debugger::tryError();
        if (is_object($replacement) || is_array($replacement)) {
            if ($replacement instanceof Callback) {
                $replacement = $replacement->getNative();
            }
            if (!is_callable($replacement, FALSE, $textual)) {
                Debugger::catchError($foo);
                throw new \Nette\InvalidStateException("Regexp: Callback '$textual' is not callable.");
            }
            $res = preg_replace_callback($this->pattern, $replacement, $subject, $limit);
            
            if (Debugger::catchError($e)) { // compile error
                $trace = $e->getTrace();
                if (isset($trace[2]['class']) && $trace[2]['class'] === __CLASS__) {
                    throw new RegexpException("Regexp: " . $e->getMessage() . " in pattern: $this->pattern");
                }
            }
            
        } else {
            $res = preg_replace($this->pattern, $replacement, $subject, $limit);
        }
        self::catchPregError($this->pattern);
        return $res;
    }
    
    
    /**
     * Perform a regular expression search and replace.
     * @param  string
     * @param  string|array
     * @param  string|callback
     * @param  int
     * @return string
     */
    public static function multiReplace($subject, $pattern, $replacement = NULL, $limit = -1) {
        Debugger::tryError();
        if (is_object($replacement) || is_array($replacement)) {
            if ($replacement instanceof Callback) {
                $replacement = $replacement->getNative();
            }
            if (!is_callable($replacement, FALSE, $textual)) {
                Debugger::catchError($foo);
                throw new \Nette\InvalidStateException("Regexp: Callback '$textual' is not callable.");
            }
            $res = preg_replace_callback($pattern, $replacement, $subject, $limit);
            
            if (Debugger::catchError($e)) { // compile error
                $trace = $e->getTrace();
                if (isset($trace[2]['class']) && $trace[2]['class'] === __CLASS__) {
                    throw new RegexpException("Regexp: " . $e->getMessage() . " in pattern: $pattern");
                }
            }
            
        } elseif (is_array($pattern)) {
            $res = preg_replace(array_keys($pattern), array_values($pattern), $subject, $limit);
            
        } else {
            $res = preg_replace($pattern, $replacement, $subject, $limit);
        }
        self::catchPregError($pattern);
        return $res;
    }


    /**
     * @internal
     * @param $pattern
     */
    public static function catchPregError($pattern) {
        if (Debugger::catchError($e)) { // compile error
            throw new RegexpException("Regexp: " . $e->getMessage() . " in pattern: $pattern");
            
        } elseif (preg_last_error()) { // run-time error
            static $messages = array(
                PREG_INTERNAL_ERROR => 'Internal error',
                PREG_BACKTRACK_LIMIT_ERROR => 'Backtrack limit was exhausted',
                PREG_RECURSION_LIMIT_ERROR => 'Recursion limit was exhausted',
                PREG_BAD_UTF8_ERROR => 'Malformed UTF-8 data',
                5 => 'Offset didn\'t correspond to the begin of a valid UTF-8 code point', // PREG_BAD_UTF8_OFFSET_ERROR
            );
            $code = preg_last_error();
            throw new RegexpException((isset($messages[$code]) ? $messages[$code] : 'Unknown error') . " (pattern: $pattern)", $code);
        }
    }
    
}
