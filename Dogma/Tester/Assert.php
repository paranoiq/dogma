<?php

namespace Dogma\Tester;

/**
 * Tester\Assert with fixed order of parameters
 */
class Assert extends \Tester\Assert
{

    public static function same($actual, $expected, $description = null)
    {
        parent::same($expected, $actual, $description);
    }

    public static function notSame($actual, $expected, $description = null)
    {
        parent::notSame($expected, $actual, $description);
    }

    public static function equal($actual, $expected, $description = null)
    {
        parent::equal($expected, $actual, $description);
    }

    public static function notEqual($actual, $expected, $description = null)
    {
        parent::notEqual($expected, $actual, $description);
    }

    public static function contains($haystack, $needle, $description = null)
    {
        parent::contains($needle, $haystack, $description);
    }

    public static function notContains($haystack, $needle, $description = null)
    {
        parent::notContains($needle, $haystack, $description);
    }

    public static function count($actualValue, $expectedCount, $description = null)
    {
        parent::count($expectedCount, $actualValue, $description);
    }

    public static function type($actualValue, $expectedType, $description = null)
    {
        parent::type($expectedType, $actualValue, $description);
    }

    public static function match($actualValue, $mask, $description = null)
    {
        parent::match($mask, $actualValue, $description);
    }

    public static function matchFile($actualValue, $file, $description = null)
    {
        parent::matchFile($file, $actualValue, $description);
    }

    public static function fail($message, $actual = null, $expected = null)
    {
        parent::fail($message, $expected, $actual);
    }

}
