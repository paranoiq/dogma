<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

/**
 * Regular expression object
 */
class Regexp
{
    use StrictBehaviorMixin;

    public const DOLLAR_MATCH_END_ONLY = 'D';
    public const CASE_INSENSITIVE = 'i';
    public const MULTILINE = 'm';
    public const DOT_MATCH_EOL = 's';
    public const UNICODE = 'u';
    public const UNGREEDY = 'U';
    public const IGNORE_WHITE_SPACE = 'x';

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
     * @param string $subject
     * @param int $flags
     * @return string[]
     */
    public function split(string $subject, int $flags = 0): array
    {
        return Str::split($subject, $this->pattern, $flags);
    }

    /**
     * Performs a regular expression match.
     * @param string $subject
     * @param int $flags
     * @param int $offset
     * @return string[]
     */
    public function match(string $subject, int $flags = 0, int $offset = 0): array
    {
        return Str::match($subject, $this->pattern, $flags, $offset);
    }

    /**
     * Performs a global regular expression match.
     * @param string $subject
     * @param int $flags (PREG_SET_ORDER is default)
     * @param int $offset
     * @return string[][]
     */
    public function matchAll(string $subject, int $flags = 0, int $offset = 0): array
    {
        return Str::matchAll($subject, $this->pattern, $flags, $offset);
    }

    /**
     * Perform a regular expression search and replace.
     * @param string $subject
     * @param string|callable $replacement
     * @param int $limit
     * @return string
     */
    public function replace(string $subject, $replacement = null, int $limit = -1): string
    {
        return Str::replace($subject, $this->pattern, $replacement, $limit);
    }

}
