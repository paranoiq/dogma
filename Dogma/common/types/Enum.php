<?php

namespace Dogma;


/**
 * Enum type
 * 
 * @property-read $cname
 * @property-read $value
 */
abstract class Enum {
    
    private static $values = array();
    private static $instances = array();
    
    private $cname;
    private $value;
    
    
    /**
     * @param string
     * @param mixed
     */
    final private function __construct($cname, $value) {
        $this->cname = $cname;
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
    final public function getCname() {
        return $this->cname;
    }
    
    
    // static ----------------------------------------------------------------------------------------------------------
    
    
    /**
     * @return \ArrayIterator
     */
    final public static function enumerate() {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);
        
        if (count(self::$values[$class]) !== count(self::$instances[$class])) { 
            foreach (self::$values[$class] as $cname => $value) {
                if (!isset(self::$instances[$class][$cname]))
                    self::$instances[$class][$cname] = new static($cname, self::$values[$class][$cname]);
            }
        }
        
        return new \ArrayIterator(self::$instances[$class]);
    }
    
    
    /**
     * @param scalar
     * @return static
     */
    final public static function instance($value) {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);
        
        foreach (self::$values[$class] as $name => $val) {
            if ($value === $val) return self::__callStatic($name, array());
        }
        
        throw new \InvalidArgumentException("Enum: Invalid value '$value' for type " . get_called_class() . ".");
    }


    /**
     * @param scalar
     * @return static
     */
    final public static function instanceByName($name) {
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
            throw new \InvalidArgumentException("Enum: Invalid name '$name' for type " . get_called_class() . ".");
        
        if (isset(self::$instances[$class][$name])) {
            return self::$instances[$class][$name];
        } else {
            self::$instances[$class][$name] = new static($name, self::$values[$class][$name]);
            return self::instance(self::$values[$class][$name]);
        }
    }
    
    
    /**
     * Get posible values
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
        throw new \Nette\MemberAccessException("Enum: Properties of Enum type are read-only.");
    }
    
    
    /**
     * @param string
     * @param mixed
     */
    final public function __unset($name) {
        throw new \Nette\MemberAccessException("Enum: Properties of Enum type are read-only.");
    }
    
    
    final public function __sleep() {
        throw new \Exception("Enum: Enum type cannot be serialized. Use its value instead.");
    }
    
    
    final public function __wakeup() {
        throw new \Exception("Enum: Enum type cannot be serialized. Use its value instead.");
    }
    
    
    final public function __clone() {
        throw new \Exception("Enum: Enum type cannot be cloned. There can be only one instance of each value.");
    }
    
}
