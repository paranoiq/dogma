<?php

namespace Dogma;

class ExceptionTypeFormater
{
    use \Dogma\StaticClassMixin;

    public static function format($type): string
    {
        if (is_array($type)) {
            return implode(' or ', array_map([self::class, 'formatType'], $type));
        } else {
            return self::formatType($type);
        }
    }

    private static function formatType($type): string
    {
        if ($type instanceof Type) {
            return $type->getId();
        } elseif (is_object($type)) {
            return get_class($type);
        } elseif (is_resource($type)) {
            return sprintf('resource(%s)', get_resource_type($type));
        } elseif (is_string($type)) {
            return $type;
        } else {
            return gettype($type);
        }
    }

}
