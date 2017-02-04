<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

abstract class PartialEnum extends \Dogma\Enum
{

    /**
     * @param int|string $value
     * @return bool
     */
    public static function isKnownValue($value): bool
    {
        return Arr::contains(static::getAllowedValues(), $value);
    }

    /**
     * @param int|string &$value
     * @return bool
     */
    public static function validateValue(&$value): bool
    {
        $regexp = '/^' . static::getValueRegexp() . '?$/';
        $result = preg_match($regexp, $value);
        if ($result === false) {
            throw new \Dogma\InvalidRegularExpressionException($regexp);
        }
        return (bool) $result;
    }

    public static function getValueRegexp(): string
    {
        throw new \LogicException(sprintf('Validation rule cannot be created automatically for class %s. Reimplement the validateValue() or getValueRegexp() method.', get_called_class()));
    }

}
