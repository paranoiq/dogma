<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Object;

/**
 * Property Accessor
 */
final class PropertyAccessor {

    /**
     * Get value from an object propperty or array key.
     * @param  object|array
     * @param  string
     * @return mixed
     */
    public static function getValue($object, $propertyName) {
        if (is_array($object)) {
            if (array_key_exists($propertyName, $data)) {
                return $object[$propertyName];
            }

        } elseif ($object instanceof \ArrayObject) {
            if ($object->hasKey($propertyName)) {
                return $object[$propertyName];
            }

        } elseif (is_object($object)) {
            $property = ReflectionCache::getPropertyReflection(get_class($object), $propertyName);
            return $property->getValue($object);
        }

        throw new \Nette\MemberAccessException("PropertyAccessor: Property '$propertyName' was not found.");
    }


    /**
     * Set value to an object property or array key.
     * @param object
     * @param string
     * @param mixed
     * @return mixed
     */
    public static function setValue($object, $propertyName, $value) {
        if (is_array($object)) {
            if (array_key_exists($propertyName, $object)) {
                $object[$propertyName] = $value;
            }

        } elseif ($object instanceof \ArrayObject) {
            if ($object->hasKey($propertyName)) {
                $object[$propertyName] = $value;
            }

        } elseif (is_object($object)) {
            $property = ReflectionCache::getPropertyReflection(get_class($object), $propertyName);
            return $property->getValue($object);
        }

        throw new \MemberAccessException("PropertyAccessor: Property '$propertyName' was not found.");
    }

}
