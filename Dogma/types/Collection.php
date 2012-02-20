<?php

namespace Dogma;

use Dogma\Object\PropertyAccessor;


class Collection extends ArrayObject {
    
    
    protected $accepted;
    
    
    public function __construct($array = array(), $acceptedClass = NULL) {
        parent::__construct($array);
        
        if ($acceptedClass) {
            $this->setAcceptedClass($acceptedClass);
            foreach ($this->data as $object) {
                $this->checkAccepted($object);
            }
        }
    }
    
    
    /**
     * Save order in the properties of items.
     * $col->indexItems(string $column);
     * @param string
     */
    public function indexItemsBy($propertyName) {
        foreach ($this->data as $key => $object) {
            PropertyAccessor::setValue($object, $propertyName, $key);
        }
    }
    
    
    // class acceptance ------------------------------------------------------------------------------------------------
    
    
    /**
     * Returns name of accepted class.
     * @return string
     */
    public function getAcceptedClass($className) {
        return $this->accepted;
    }
    
    
    /**
     * Check if object is of accepted class.
     * @param  object
     * @throws \InvalidArgumentException
     */
    protected function checkAccepted($object) {
        if (!$object instanceof $this->accepted)
            throw new \InvalidArgumentException("Collection: Inserted object is not of the accepted class.");
    }
    
    
    /**
     * Adds items to the end of array.
     * @param array
     */
    public function append($items) {
        foreach ($items as $item) {
            $this->checkAccepted($item);
        }
        parent::append($items);
    }
    
    
    /**
     * Adds items to the beginning of array. Does not preserve keys.
     * @param array
     */
    public function prepend($items) {
        foreach (array_reverse($items) as $item) {
            $this->checkAccepted($item);
        }
        parent::prepend($items);
    }
    
    
    /**
     * Insert items at given position. Does not preserve keys.
     * @param array
     * @param integer
     */
    public function insertAt($items, $position) {
        foreach ($items as $item) {
            $this->checkAccepted($item);
        }
        parent::insertAt($items, $position);
    }
    
    
    /** ArrayAccess interface */
    public function offsetSet($key, $value) {
        $this->checkAccepted($value);
        parent::offsetSet($key, $value);
    }
    
}
