<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Enum;

use Dogma\Arr;
use Dogma\InvalidRegularExpressionException;
use function preg_match;
use function sprintf;

abstract class PartialIntEnum extends IntEnum
{

    public static function isKnownValue(int $value): bool
    {
        return Arr::contains(static::getAllowedValues(), $value);
    }

    public static function validateValue(int &$value): bool
    {
        $regexp = '/^' . static::getValueRegexp() . '?$/';
        $result = preg_match($regexp, $value);
        if ($result === false) {
            throw new InvalidRegularExpressionException($regexp);
        }
        return (bool) $result;
    }

    public static function getValueRegexp(): string
    {
        throw new \LogicException(sprintf('Validation rule cannot be created automatically for class %s. Reimplement the validateValue() or getValueRegexp() method.', static::class));
    }

}
