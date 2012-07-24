<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


/**
 * Enum type. Simillar to Enum from Java. Allowed values are defined as class constants.
 * 
 * @property-read $identifier
 * @property-read $value
 */
abstract class Enum implements SimpleValueObject, IndirectInstantiable {
    
    private static $values = array();
    private static $instances = array();
    
    private $identifier;
    private $value;
    
    
    /**
     * @param string
     * @param mixed
     */
    final private function __construct($identifier, $value) {
        $this->identifier = $identifier;
        $this->value = $value;
    }
    
    
    /**
     * @return string
     */
    final public function __toString() {
        return (string) $this->value;
    }
    
    
    /**
     * @return mixed 
     */
    final public function getValue() {
        return $this->value;
    }


    /**
     * @return mixed
     */
    final public function getIdentifier() {
        return $this->identifier;
    }
    
    
    // static ----------------------------------------------------------------------------------------------------------


    /**
     * @param mixed
     * @return bool
     */
    final public static function isValid($value) {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);

        return in_array($value, self::$values[$class]);
    }


    /**
     * Get possible values.
     * @return array
     */
    final public static function getAllowedValues() {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);
        
        return self::$values[$class];
    }
    
    
    /**
     * Get all values as Enum objects.
     * @return \ArrayIterator
     */
    final public static function enumerate() {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);
        
        if (count(self::$values[$class]) !== count(self::$instances[$class])) { 
            foreach (self::$values[$class] as $identifier => $value) {
                if (!isset(self::$instances[$class][$identifier]))
                    self::$instances[$class][$identifier] = new static($identifier, self::$values[$class][$identifier]);
            }
        }
        
        return new \ArrayIterator(self::$instances[$class]);
    }
    
    
    /**
     * @param scalar
     * @return static
     */
    final public static function getInstance($value) {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);
        
        foreach (self::$values[$class] as $name => $val) {
            if ($value === $val) return self::__callStatic($name, array());
        }
        
        throw new \InvalidArgumentException("Invalid value '$value' for type " . get_called_class() . ".");
    }


    /**
     * @param scalar
     * @return static
     */
    final public static function getInstanceByName($name) {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);

        return self::__callStatic($name, array());
    }
    
    
    /**
     * @param string
     * @param array
     * @return static
     */
    final public static function __callStatic($name, $args) {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);

        if (!isset(self::$values[$class][$name]))
            throw new \InvalidArgumentException("Invalid name '$name' for type " . get_called_class() . ".");
        
        if (isset(self::$instances[$class][$name])) {
            return self::$instances[$class][$name];
            
        } else {
            self::$instances[$class][$name] = new static($name, self::$values[$class][$name]);
            return self::getInstance(self::$values[$class][$name]);
        }
    }
    
    
    /**
     * @param string
     */
    final private static function init($class) {
        $ref = new \ReflectionClass($class);
        self::$values[$class] = $ref->getConstants();
        self::$instances[$class] = array();
    }
    
    
    // magic motherfucker ----------------------------------------------------------------------------------------------
    
    
    /**
     * @param string
     * @return mixed
     */
    final public function __get($name) {
        return \Nette\ObjectMixin::get($this, $name);
    }
    
    
    /**
     * @param string
     * @return mixed
     */
    final public function __isset($name) {
        return \Nette\ObjectMixin::has($this, $name);
    }


    /**
     * @param string
     * @param mixed
     */
    final public function __set($name, $value) {
        throw new \Nette\MemberAccessException("Properties of Enum type are read-only.");
    }


    /**
     * @param string
     */
    final public function __unset($name) {
        throw new \Nette\MemberAccessException("Properties of Enum type are read-only.");
    }
    
    
    final public function __sleep() {
        throw new \Exception("Enum type cannot be serialized. Use its value instead.");
    }
    
    
    final public function __wakeup() {
        throw new \Exception("Enum type cannot be serialized. Use its value instead.");
    }
    
    
    final public function __clone() {
        throw new \Exception("Enum type cannot be cloned. There can be only one instance of each value.");
    }
    
}
