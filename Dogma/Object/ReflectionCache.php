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


final class ReflectionCache {


    private static $classes = array();
    private static $properties = array();
    private static $allProps = array();


    /**
     * Returns class reflection.
     * @param  string
     * @return \ReflectionClass
     */
    public static function getClassReflection($className) {
        if (!isset(self::$classes[$className])) {
            self::$classes[$className] = new ClassType($className);
        }
        return self::$classes[$className];
    }


    /**
     * Returns unlocked reflection of an object property.
     * @param  string
     * @param  string
     * @return \ReflectionProperty
     * @throws \MemberAccessException
     */
    public static function getPropertyReflection($className, $propertyName) {
        if (!isset(self::$classes[$className])) {
            $class = self::getClassReflection($className);
            if ($property = $class->getProperty($propertyName)) {
                $property->setAccessible(TRUE);
                self::$properties[$className][$propertyName] = $property;
            } else {
                throw new \Nette\MemberAccessException("PropertyCache: Class '$className' has not a property named '$propertyName'.");
            }
        }
        return self::$properties[$className][$propertyName];
    }


    /**
     * Returns class reflection and unlocked reflections of its properties.
     * @param string
     * @return array(\ReflectionClass, array(\ReflectionProperty))
     */
    public static function getClassAndPropertyReflections($className) {
        if (!isset($allProps[$className])) {
            $class = self::getClassReflection($className);
            $properties = $class->getProperties();
            foreach ($properties as $property) {
                $property->setAccessible(TRUE);
            }
            self::$allProps[$className] = $properties;
        }
        return array(self::$classes[$className], self::$allProps[$className]);
    }

}
