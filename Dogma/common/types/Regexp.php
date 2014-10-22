<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Nette\Utils\Strings;


/**
 * Regular expression object
 */
class Regexp extends \Dogma\Object {

    const DOLLAR_MATCH_END_ONLY = 'D';
    const CASE_INSENSITIVE = 'i';
    const MULTILINE = 'm';
    const DOT_MATCH_EOL = 's';
    const UNICODE = 'u';
    const UNGREEDY = 'U';
    const IGNORE_WHITE_SPACE = 'x';


    /** @var string */
    private $pattern;


    /**
     * @param string
     */
    public function __construct($pattern) {
        $this->pattern = $pattern;
    }


    /**
     * @return string
     */
    public function __toString() {
        return $this->pattern;
    }


    /**
     * Splits string by a regular expression.
     * @param string
     * @param integer
     * @return string[]
     */
    public function split($subject, $flags = 0) {
        return Strings::split($subject, $this->pattern, $flags);
    }


    /**
     * Performs a regular expression match.
     * @param string
     * @param integer
     * @param integer
     * @return string[]
     */
    public function match($subject, $flags = 0, $offset = 0) {
        return Strings::match($subject, $this->pattern, $flags, $offset);
    }


    /**
     * Performs a global regular expression match.
     * @param string
     * @param integer (PREG_SET_ORDER is default)
     * @param integer
     * @return string[][]
     */
    public function matchAll($subject, $flags = 0, $offset = 0) {
        return Strings::matchAll($subject, $this->pattern, $flags, $offset);
    }


    /**
     * Perform a regular expression search and replace.
     * @param string
     * @param string|callback
     * @param integer
     * @return string
     */
    public function replace($subject, $replacement = null, $limit = -1) {
        return Strings::replace($subject, $this->pattern, $replacement, $limit);
    }

}
