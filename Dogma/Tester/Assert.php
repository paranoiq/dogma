<?php

namespace Dogma\Tester;

/**
 * Tester\Assert with fixed order of parameters
 */
class Assert extends \Tester\Assert
{

    public static function same($actual, $expected)
    {
        parent::same($expected, $actual);
    }

    public static function notSame($actual, $expected)
    {
        parent::notSame($expected, $actual);
    }

    public static function equal($actual, $expected)
    {
        parent::equal($expected, $actual);
    }

    public static function notEqual($actual, $expected)
    {
        parent::notEqual($expected, $actual);
    }

    public static function contains($haystack, $needle)
    {
        parent::contains($needle, $haystack);
    }

    public static function notContains($haystack, $needle)
    {
        parent::notContains($needle, $haystack);
    }

    public static function count($actualValue, $expectedCount)
    {
        parent::count($expectedCount, $actualValue);
    }

    public static function type($actualValue, $expectedType)
    {
        parent::type($expectedType, $actualValue);
    }

    public static function match($actualValue, $mask)
    {
        parent::match($mask, $actualValue);
    }

    public static function matchFile($actualValue, $file)
    {
        parent::matchFile($file, $actualValue);
    }

    public static function fail($message, $actual = null, $expected = null)
    {
        parent::fail($message, $expected, $actual);
    }

}
