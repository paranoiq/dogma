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
 * Creates and manages object prototypes
 */
final class Prototyper
{


    /**
     * Create instance of class and inject it with data.
     * @param string
     * @param array
     * @param array
     * @return object
     */
    public static function createInstance($class, &$data, $aliases = [])
    {
        $object = self::getPrototype($class);

        foreach ($aliases as $orig => $alias) {
            $data[$alias] = $data[$orig];
            unset($data[$orig]);
        }

        self::injectData($object, $data, false);
        if ($data) {
            throw new \InvalidArgumentException('Prototyper: Incomplete data injection.');
        }

        return $object;
    }


    /**
     * Returns prototype of object of given class without calling constructor and clonning
     * @param string
     * @return object
     */
    public static function getPrototype($class)
    {
        // deserializace je ca 1.9x pomalejší než klonování. zpomalení cast() o méně než 10%
        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));

        //static $prototypes = [];
        //
        //if (!isset($prototypes[$class])) {
        //    $prototypes[$class] = unserialize(sprintf('O:%d:"%s":0:{}', strlen($class), $class));
        //}
        //return clone $prototypes[$class];
    }


    /**
     * Injects data into object properties
     * @param object $object
     * @param array $data injected keys will be removed from array
     * @param boolean $respectPrivatePropertyDefiner set fo false if you want inject private property without the concern of definer class
     * @return object
     */
    public static function injectData($object, &$data, $respectPrivatePropertyDefiner = true)
    {
        /** @var \ReflectionClass $class */
        /** @var \ReflectionProperty[] $properties */
        list($class, $properties) = ReflectionCache::getClassAndPropertyReflections(get_class($object));

        do {
            foreach ($properties as $property) {
                if ($property->isStatic()) {
                    continue;
                }

                $value = $respectPrivatePropertyDefiner && $property->isPrivate()
                    ? self::extractPrivateValue($data, $property->getName(), $class->getName())
                    : self::extractValue($data, $property->getName());
                if ($value !== null) {
                    $property->setValue($object, $value);
                }
            }
            if (!$class = $class->getParentClass()) {
                break;
            }
            list($class, $properties) = ReflectionCache::getClassAndPropertyReflections($class->getName());
        } while (true);

        return $object;
    }


    /**
     * Takes value of property from given array
     * @param string
     * @param array
     * @return mixed
     */
    private static function extractValue(&$data, $propertyName)
    {
        if (array_key_exists($propertyName, $data)) {
            $value = $data[$propertyName];
            unset($data[$propertyName]);
            return $value;
        }

        $key = sprintf('\x00*\x00%s', $propertyName);
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
            unset($data[$key]);
            return $value;
        }

        return null;
    }


    /**
     * Takes value of private property from given array
     * @param array
     * @param string
     * @param string
     * @return mixed
     */
    private static function extractPrivateValue(&$data, $propertyName, $className)
    {
        $key = sprintf('\x00%s\x00%s', $className, $propertyName);
        if (array_key_exists($key, $data)) {
            $value = $data[$key];
            unset($data[$key]);
            return $value;
        }

        return null;
    }

}
