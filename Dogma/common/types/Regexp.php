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
class Regexp extends \Dogma\Object
{

    const DOLLAR_MATCH_END_ONLY = 'D';
    const CASE_INSENSITIVE = 'i';
    const MULTILINE = 'm';
    const DOT_MATCH_EOL = 's';
    const UNICODE = 'u';
    const UNGREEDY = 'U';
    const IGNORE_WHITE_SPACE = 'x';


    /** @var string */
    private $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    public function __toString(): string
    {
        return $this->pattern;
    }

    /**
     * Splits string by a regular expression.
     * @param string
     * @param int
     * @return string[]
     */
    public function split(string $subject, int $flags = 0): array
    {
        return Strings::split($subject, $this->pattern, $flags);
    }

    /**
     * Performs a regular expression match.
     * @param string
     * @param int
     * @param int
     * @return string[]
     */
    public function match(string $subject, int $flags = 0, int $offset = 0): array
    {
        return Strings::match($subject, $this->pattern, $flags, $offset);
    }

    /**
     * Performs a global regular expression match.
     * @param string
     * @param int (PREG_SET_ORDER is default)
     * @param int
     * @return string[][]
     */
    public function matchAll(string $subject, int $flags = 0, int $offset = 0): array
    {
        return Strings::matchAll($subject, $this->pattern, $flags, $offset);
    }

    /**
     * Perform a regular expression search and replace.
     * @param string
     * @param string|callback
     * @param int
     * @return string
     */
    public function replace(string $subject, $replacement = null, int $limit = -1): string
    {
        return Strings::replace($subject, $this->pattern, $replacement, $limit);
    }

}
