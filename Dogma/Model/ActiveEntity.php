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


    /** @var \Nette\Database\Table\ActiveRow */
    protected $row;

    /** @var EntityFactory */
    private $factory;

    /** @var array property objects cache */
    private $props = array();


    public function __construct(\Nette\Database\Table\ActiveRow $row, EntityFactory $factory) {
        $this->row = $row;
        $this->factory = $factory;

        foreach ($factory->getMagicProperties(get_called_class()) as $name) {
            unset($this->$name);
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
    public function getTable($table = null) {
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

        } elseif ($this->factory->hasMagicProperty($class = get_called_class(), $name)) {
            $var = $this->factory->createPropertyInstance($class, $name, $this->row);
            $this->props[$name] = $var;

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

        } elseif ($this->factory->hasMagicProperty($class = get_called_class(), $name)) {
            $this->props[$name] = $this->factory->updatePropertyInstance($class, $name, $value, $this->row);

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
