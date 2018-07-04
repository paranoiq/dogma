<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tester;

use Dogma\Equalable;
use const SORT_STRING;
use function abs;
use function array_keys;
use function current;
use function get_class;
use function is_array;
use function is_finite;
use function is_float;
use function is_object;
use function ksort;
use function max;
use function next;

/**
 * Tester\Assert with fixed order of parameters
 * Added support for comparing object with Equalable interface
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
     * Added support for comparing object with Equalable interface
     * @param mixed $actual
     * @param mixed $expected
     * @param string|mixed|null $description
     */
    public static function equal($actual, $expected, $description = null): void
    {
        if ($actual instanceof Equalable && $expected instanceof Equalable && get_class($actual) === get_class($expected)) {
            self::$counter++;
            if (!$actual->equals($expected)) {
                self::fail(self::describe('%1 should be equal to %2', $description), $expected, $actual);
            }
        } else {
            self::$counter++;
            if (!self::isEqual($expected, $actual)) {
                self::fail(self::describe('%1 should be equal to %2', $description), $expected, $actual);
            }
        }
    }

    /**
     * Added support for comparing object with Equalable interface
     * @param mixed $actual
     * @param mixed $expected
     * @param string|mixed|null $description
     */
    public static function notEqual($actual, $expected, $description = null): void
    {
        if ($actual instanceof Equalable && $expected instanceof Equalable && get_class($actual) === get_class($expected)) {
            self::$counter++;
            if ($actual->equals($expected)) {
                self::fail(self::describe('%1 should not be equal to %2', $description), $expected, $actual);
            }
        } else {
            self::$counter++;
            if (self::isEqual($expected, $actual)) {
                self::fail(self::describe('%1 should not be equal to %2', $description), $expected, $actual);
            }
        }
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

    /**
     * Added support for comparing object with Equalable interface
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param mixed $level
     * @param mixed|null $objects
     * @return bool
     * @internal
     */
    public static function isEqual($expected, $actual, $level = 0, $objects = null): bool
    {
        if ($level > 10) {
            throw new \Exception('Nesting level too deep or recursive dependency.');
        }

        if (is_float($expected) && is_float($actual) && is_finite($expected) && is_finite($actual)) {
            $diff = abs($expected - $actual);
            return ($diff < self::EPSILON) || ($diff / max(abs($expected), abs($actual)) < self::EPSILON);
        }

        if (is_object($expected) && is_object($actual) && get_class($expected) === get_class($actual)) {
            /* start */
            if ($expected instanceof Equalable && $actual instanceof Equalable) {
                return $expected->equals($actual);
            }
            /* end */
            $objects = $objects ? clone $objects : new \SplObjectStorage();
            if (isset($objects[$expected])) {
                return $objects[$expected] === $actual;
            } elseif ($expected === $actual) {
                return true;
            }
            $objects[$expected] = $actual;
            $objects[$actual] = $expected;
            $expected = (array) $expected;
            $actual = (array) $actual;
        }

        if (is_array($expected) && is_array($actual)) {
            ksort($expected, SORT_STRING);
            ksort($actual, SORT_STRING);
            if (array_keys($expected) !== array_keys($actual)) {
                return false;
            }

            foreach ($expected as $value) {
                if (!self::isEqual($value, current($actual), $level + 1, $objects)) {
                    return false;
                }
                next($actual);
            }
            return true;
        }

        return $expected === $actual;
    }

    /**
     * @param mixed $reason
     * @param mixed $description
     * @return string
     */
    private static function describe($reason, $description): string
    {
        return ($description ? $description . ': ' : '') . $reason;
    }

}
