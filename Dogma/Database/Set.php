<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;


/**
 * Representation of database type SET
 */
class Set /*extends \Dogma\Object*/ implements \ArrayAccess, \IteratorAggregate {
    
    protected $values = array();
    
    public $onChange = array();
    
    
    /**
     * @param array
     */
    public function __construct($set = array()) {
        if (is_string($set) && $set) {
            $values = explode(',', $set);
            foreach ($values as $val) {
                $this->values[$val] = TRUE;
            }
            
        } elseif (is_array($set)) {
            foreach ($set as $name => $value) {
                $this->__set($name, $value);
            }
            
        } elseif ($set instanceof self) {
            /** @noinspection PhpUndefinedFieldInspection */
            $this->values = $set->values;
        }
    }
    
    
    /**
     * Set more values at once
     * @param array(name=>bool)
     */
    public function setValues($values) {
        foreach ($values as $name => $value) {
            $this->__set($name, $value);
        }
    }


    /**
     * @return string
     */
    public function __toString() {
        return implode(',', array_keys($this->values));
    }


    /**
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->values);
    }


    /**
     * @param string
     * @param string
     */
    public function offsetSet($key, $value) {
        $this->__set($key, $value);
    }


    /**
     * @param string
     * @return bool
     */
    public function offsetGet($key) {
        return $this->__get($key);
    }


    /**
     * @param string
     * @return bool
     */
    public function offsetExists($key) {
        return $this->__isset($key);
    }


    /**
     * @param string
     */
    public function offsetUnset($key) {
        $this->__unset($key);
    }


    /**
     * @param string
     * @return bool
     */
    public function __get($name) {
        if (strpos($name, ',') !== FALSE)
            throw new \InvalidArgumentException("Invalid SET value.");
        
        if (isset($this->values[$name])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    /**
     * @param string
     * @param string
     */
    public function __set($name, $value) {
        if (strpos($name, ',') !== FALSE)
            throw new \InvalidArgumentException("Invalid SET value.");
        
        $this->onChange();
        if ($value) {
            $this->values[$name] = TRUE;
        } else {
            unset($this->values[$name]);
        }
    }


    /**
     * @param $name
     * @return bool
     */
    public function __isset($name) {
        if (strpos($name, ',') !== FALSE)
            throw new \InvalidArgumentException("Invalid SET value.");
        
        if (isset($this->values[$name])) {
            return TRUE;
        } else {
            return FALSE;
        }
    }


    /**
     * @param string
     */
    public function __unset($name) {
        if (strpos($name, ',') !== FALSE)
            throw new \InvalidArgumentException("Invalid SET value.");
        
        $this->onChange();
        unset($this->values[$name]);
    }


    /**
     * @param string
     * @param array
     * @return mixed
     */
    public function __call($name, $args) {
        return \Nette\ObjectMixin::call($this, $name, $args);
    }
    
}
