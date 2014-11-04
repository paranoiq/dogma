<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Object;

use Nette\Reflection\ClassType;


final class ReflectionCache
{


    private static $classes = [];
    private static $properties = [];
    private static $allProps = [];

    /**
     * Returns class reflection.
     * @param string
     * @return \ReflectionClass
     */
    public static function getClassReflection($className)
    {
        if (!isset(self::$classes[$className])) {
            self::$classes[$className] = new ClassType($className);
        }
        return self::$classes[$className];
    }

    /**
     * Returns unlocked reflection of an object property.
     * @param string
     * @param string
     * @return \ReflectionProperty
     * @throws \Nette\MemberAccessException
     */
    public static function getPropertyReflection($className, $propertyName)
    {
        if (!isset(self::$classes[$className])) {
            $class = self::getClassReflection($className);
            if ($property = $class->getProperty($propertyName)) {
                $property->setAccessible(true);
                self::$properties[$className][$propertyName] = $property;
            } else {
                throw new \Nette\MemberAccessException(sprintf('PropertyCache: Class \'%s\' has not a property named \'%s\'.', $className, $propertyName));
            }
        }
        return self::$properties[$className][$propertyName];
    }

    /**
     * Returns class reflection and unlocked reflections of its properties.
     * @param string
     * @return array (\ReflectionClass, (\ReflectionProperty))
     */
    public static function getClassAndPropertyReflections($className)
    {
        if (!isset($allProps[$className])) {
            $class = self::getClassReflection($className);
            $properties = $class->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(true);
            }
            self::$allProps[$className] = $properties;
        }
        return [self::$classes[$className], self::$allProps[$className]];
    }

}
