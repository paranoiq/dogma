<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Model;

use Dogma\Language\Inflector;


/**
 * Base of entity objects.
 * - holds a table row
 * - transforms and validates value-objects (@instance)
 * - translates names from underscore to camel case
 */
class ActiveEntity extends \Dogma\Object implements \ArrayAccess, \IteratorAggregate {
    
    /** @var array ($entityClass => array($propertyName => array($propertyClass, array($paramName => $paramType)))) */
    private static $meta = array();
    
    /** @var \Nette\Database\Table\ActiveRow */
    private $row;

    /** @var array property objects cache */
    private $props = array();

    
    public function __construct(\Nette\Database\Table\ActiveRow $row) {
        $this->row = $row;
        
        foreach ($this->getMagicProperties() as $name) {
            unset($this->$name);
        }
    }


    /**
     * @return string[]
     */
    private function getMagicProperties() {
        $class = get_called_class();
        if (array_key_exists($class, self::$meta))
            return array_keys(self::$meta[$class]);
        
        $ns = preg_replace('/[^\\\\]+$/', '', $class);
        $ref = new \Nette\Reflection\ClassType($this);

        $props = array();
        foreach ($ref->getProperties() as $property) {
            if ($property->isPublic()
                && ($meta = $property->getAnnotation('instance'))
                && ($type = $property->getAnnotation('var'))
            ) {
                $type = $this->getClassName($ns, $type);
                
                if ($meta === TRUE) {
                    $props[$property->getName()] = array($type, array($property->getName() => NULL));
                    
                } else {
                    $params = array();
                    foreach ($meta as $param) {
                        @list($a, $b) = explode(' ', $param);
                        $paramName = $b ?: $a;
                        $paramType = $b ? $a : NULL;
                        $params[str_replace('$', '', $paramName)] = $paramType;
                    }
                    
                    $props[$property->getName()] = array($type, $params);
                }
            }
        }
        self::$meta[$class] = $props;
        
        return array_keys($props);
    }


    /**
     * @param string
     * @param string
     * @return string
     */
    private function getClassName($ns, $type) {
        @list($type) = preg_split('/[\\s|]/', $type);
        if ($type[0] === '\\')
            return $type;
        
        return $ns . $type;
    }
    
    
    /**
     * @param string
     * @param string
     * @return object
     */
    private function getPropertyInstance($ec, $name) {
        list($class, $params) = self::$meta[$ec][$name];
        
        $args = array();
        foreach ($params as $name => $type) {
            $args[] = ($type === NULL)
                ? $this->row->__get(Inflector::underscore($name))
                : $this->createInstance($type, array($this->row->__get(Inflector::underscore($name))));
        }
        
        $instance = $this->createInstance($class, $args);
        
        $this->props[$name] = $instance;
        
        return $instance;
    }


    /**
     * @param string
     * @param string
     * @param mixed
     */
    private function updatePropertyInstance($ec, $name, $value) {
        list($class, $params) = self::$meta[$ec][$name];
        
         if ($value instanceof $class) {
            $instance = $value;
            
            if ($value instanceof \Dogma\CompoundValueObject) {
                $parts = array_combine(array_keys($params), array_values($value->toArray()));
                if (!$parts)
                    throw new \LogicException("Count of fields returned by CompoundValueObject does not fit the count of fields in constructor.");
                
                foreach ($parts as $key => $val) {
                    $this->row->__set($key, $val);
                }
            } else {
                $this->row->__set($name, $value);
            }
            
        } else {
            $instance = $this->createInstance($class, array($value));
            $this->row->__set($name, $instance);
        }

        $this->props[$name] = $instance;
    }
    

    /**
     * @param string
     * @param array
     * @return object
     */
    private function createInstance($class, $args) {
        $ref = new \Nette\Reflection\ClassType($class);
        
        if ($ref->implementsInterface('Dogma\\IndirectInstantiable')) {
            return call_user_func_array(array($class, 'getInstance'), $args);
            
        } else {
            return $ref->newInstanceArgs($args);
        }
    }


    /**
     * Save modification to database.
     * @return bool
     */
    public function save() {
        return (bool) $this->row->update();
    }


    /**
     * Delete entity from database.
     * @return bool
     */
    public function delete() {
        return (bool) $this->row->delete();
    }


    /**
     * Get table selection
     * @return \Nette\Database\Table\Selection
     */
    public function getTable($table = NULL) {
        if (empty($table)) {
            return $this->row->getTable();
        } else {
            return $this->row->getTable()->getConnection()->table($table);
        }
    }
    

    // interfaces ------------------------------------------------------------------------------------------------------

    
    /**
     * @return \ArrayIterator
     */
    public function getIterator() {
        /// iterate entity fields?
        return $this->row->getIterator();
    }
    
    
    /**
     * @param string
     * @return mixed
     */
    public function &__get($name) {
        if (isset($props[$name])) {
            $var = $props[$name];
            
        } elseif (method_exists($this, "get$name")) {
            $var = call_user_func(array($this, "get$name"));
            
        } elseif (isset(self::$meta[$class = get_called_class()][$name])) {
            $var = $this->getPropertyInstance($class, $name);
            
        } else {
            $var = $this->row->__get(Inflector::underscore($name));
        }
        
        return $var;
    }


    /**
     * @param string
     * @param mixed
     */
    public function __set($name, $value) {
        if (method_exists($this, "set$name")) {
            call_user_func(array($this, "set$name"), $value);
            
        } elseif (isset(self::$meta[$class = get_called_class()][$name])) {
            $this->updatePropertyInstance($class, $name, $value);
            
        } else {
            $this->row->__set(Inflector::underscore($name), $value);
        }
    }


    /**
     * @param string
     * @return bool
     */
    public function __isset($name) {
        return $this->row->__isset(Inflector::underscore($name));
    }


    /**
     * @param string
     */
    public function __unset($name) {
        $this->row->__unset(Inflector::underscore($name));
    }


    /**
     * @param string
     * @return mixed
     */
    public function offsetGet($name) {
        return $this->__get($name);
    }

    
    /**
     * @param string
     * @param mixed
     */
    public function offsetSet($name, $value) {
        $this->__set($name, $value);
    }

    
    /**
     * @param string
     * @return bool
     */
    public function offsetExists($name) {
        return $this->__isset($name);
    }


    /**
     * @param string
     */
    public function offsetUnset($name) {
        $this->__unset($name);
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->row->__toString();
    }
    
}
