<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tester;

/**
 * Tester\Assert with fixed order of parameters
 */
class Assert extends \Tester\Assert
{

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @param string|mixed|null $description
     */
    public static function same($actual, $expected, $description = null): void
    {
        parent::same($expected, $actual, $description);
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @param string|mixed|null $description
     */
    public static function notSame($actual, $expected, $description = null): void
    {
        parent::notSame($expected, $actual, $description);
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @param string|mixed|null $description
     */
    public static function equal($actual, $expected, $description = null): void
    {
        parent::equal($expected, $actual, $description);
    }

    /**
     * @param mixed $actual
     * @param mixed $expected
     * @param string|mixed|null $description
     */
    public static function notEqual($actual, $expected, $description = null): void
    {
        parent::notEqual($expected, $actual, $description);
    }

    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @param string|mixed|null $description
     */
    public static function contains($haystack, $needle, $description = null): void
    {
        parent::contains($needle, $haystack, $description);
    }

    /**
     * @param mixed $haystack
     * @param mixed $needle
     * @param string|mixed|null $description
     */
    public static function notContains($haystack, $needle, $description = null): void
    {
        parent::notContains($needle, $haystack, $description);
    }

    /**
     * @param mixed $actualValue
     * @param int|mixed $expectedCount
     * @param string|mixed|null $description
     */
    public static function count($actualValue, $expectedCount, $description = null): void
    {
        parent::count($expectedCount, $actualValue, $description);
    }

    /**
     * @param mixed $actualValue
     * @param string|mixed $expectedType
     * @param string|mixed|null $description
     */
    public static function type($actualValue, $expectedType, $description = null): void
    {
        parent::type($expectedType, $actualValue, $description);
    }

    /**
     * @param mixed $actualValue
     * @param string|mixed $mask
     * @param string|mixed|null $description
     */
    public static function match($actualValue, $mask, $description = null): void
    {
        parent::match($mask, $actualValue, $description);
    }

    /**
     * @param mixed $actualValue
     * @param mixed $file
     * @param string|mixed|null $description
     */
    public static function matchFile($actualValue, $file, $description = null): void
    {
        parent::matchFile($file, $actualValue, $description);
    }

    /**
     * @param string|mixed $message
     * @param mixed|null $actual
     * @param mixed|null $expected
     */
    public static function fail($message, $actual = null, $expected = null): void
    {
        parent::fail($message, $expected, $actual);
    }

}
