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
 * Set type. Similar to set from MySql. Allowed values are defined as class constants.
 */
abstract class Set /*extends \Dogma\Object*/ {

    private static $values = array();

    private $set = array();
    
    
    /**
     * @param self|string|string[]
     */
    final public function __construct($set = array()) {
        $this->add($set);
    }
    
    
    /**
     * @param self|string|string[]
     * @return static
     */
    public function add($set) {
        $this->checkSet($set);

        foreach ($set as $value) {
            if (!self::isValid($value))
                throw new \InvalidArgumentException("Invalid value given.");
            
            if (!in_array($value, $this->set)) {
                $this->set[] = $value;
            }
        }
        
        return $this;
    }
    
    
    /**
     * @param self|string|string[]
     * @return Set
     */
    public function remove($set) {
        $this->checkSet($set);
        
        foreach ($set as $i => $value) {
            if (in_array($value, $this->set)) {
                unset($this->set[$i]);
            }
        }
        
        return $this;
    }


    /**
     * @param self|string|string[]
     * @return bool
     */
    public function contains($set) {
        $this->checkSet($set);

        foreach ($set as $value) {
            if (!in_array($value, $this->set))
                return FALSE;
        }

        return TRUE;
    }
    

    /**
     * @param self|string|string[]
     */
    private function checkSet(&$set) {
        if (is_string($set)) {
            $set = explode(',', $set);
            
        } elseif ($set instanceof self) { // is_object($set) && get_class($set) === get_called_class() ?
            $set = $set->getValues();
            
        } elseif (!is_array($set)) {
            throw new \InvalidArgumentException("Value must be a string, array or a Set.");
        }
    }
    
    
    /**
     * @return string
     */
    final public function __toString() {
        return implode(',', $this->set);
    }

    
    /**
     * @return array
     */
    final public function getValues() {
        return $this->set;
    }


    /**
     * Set more values at once
     * @param array(name=>bool)
     * @return self
     */
    public function setValues($values) {
        foreach ($values as $name => $value) {
            $this->__set($name, $value);
        }
        
        return $this;
    }
    
    
    // static ----------------------------------------------------------------------------------------------------------


    /**
     * @param string|string[]
     * @return bool
     */
    final public static function isValid($value) {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);

        if (is_array($value)) {
            foreach ($value as $val) {
                if (!self::isValid($val))
                    return FALSE;
            }
            return TRUE;
        }

        return in_array($value, self::$values[$class]);
    }
    
    
    /**
     * Get possible values.
     * @return \ArrayIterator
     */
    public static function getAllowedValues() {
        if (!isset(self::$values[$class = get_called_class()])) self::init($class);

        return new \ArrayIterator(self::$values[$class]);
    }


    /**
     * @param string
     */
    final private static function init($class) {
        $ref = new \ReflectionClass($class);
        self::$values[$class] = $ref->getConstants();
    }
    

    // magic motherfucker ----------------------------------------------------------------------------------------------


    /**
     * @param string
     * @return mixed
     */
    final public function __get($name) {
        if (self::isValid($name))
            return $this->contains($name);
        
        return \Nette\ObjectMixin::get($this, $name);
    }


    /**
     * @param string
     * @return mixed
     */
    final public function __isset($name) {
        if (self::isValid($name))
            return $this->contains($name);
        
        return \Nette\ObjectMixin::has($this, $name);
    }


    /**
     * @param string
     * @param mixed
     */
    final public function __set($name, $value) {
        if (self::isValid($name)) {
            if (is_string($value)) {
                $norm = new Normalizer;
                $bool = $norm->detectBool($value);
                if (isset($bool)) $value = $bool;
            }
            
            $value ? $this->add($name) : $this->remove($name);
            return;
        }

        \Nette\ObjectMixin::set($this, $name, $value);
    }


    /**
     * @param string
     * @param mixed
     */
    final public function __unset($name) {
        if (self::isValid($name)) {
            $this->remove($name);
        }

        \Nette\ObjectMixin::remove($this, $name);
    }


    final public function __sleep() {
        throw new \Exception("Set type cannot be serialized. Use its values instead.");
    }


    final public function __wakeup() {
        throw new \Exception("Set type cannot be serialized. Use its values instead.");
    }


    /*final public function __clone() {
        throw new \Exception("Set type cannot be cloned. There can be only one instance of each value.");
    }*/
    
}
