<?php

namespace Dogma;

abstract class PartialEnum extends \Dogma\Enum
{

    /**
     * @param int|string $value
     * @return bool
     */
    public static function isKnownValue($value): bool
    {
        return Arr::contains(self::getAllowedValues(), $value);
    }

    /**
     * @param int|string &$value
     * @return bool
     */
    public static function validateValue(&$value): bool
    {
        $regexp = '/^' . self::getValueRegexp() . '?$/';
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
