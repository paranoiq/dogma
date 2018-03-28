<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class ExceptionTypeFormatter
{
    use StaticClassMixin;

    /**
     * @param mixed $type
     * @return string
     */
    public static function format($type): string
    {
        if (is_array($type)) {
            return implode(' or ', array_map([self::class, 'formatType'], $type));
        } else {
            return self::formatType($type);
        }
    }

    /**
     * @param mixed $type
     * @return string
     */
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
