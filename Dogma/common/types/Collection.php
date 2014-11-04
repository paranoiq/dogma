<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Dogma\Object\PropertyAccessor;


class Collection extends ArrayObject
{

    /** @var string */
    protected $accepted;

    /**
     * @param object[] $array
     * @param string $acceptedClass
     */
    public function __construct($array = [], $acceptedClass = null)
    {
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
    public function indexItemsBy($propertyName)
    {
        foreach ($this->data as $key => $object) {
            PropertyAccessor::setValue($object, $propertyName, $key);
        }
    }

    // class acceptance ------------------------------------------------------------------------------------------------

    /**
     * Returns name of accepted class.
     * @return string
     */
    public function getAcceptedClass()
    {
        return $this->accepted;
    }

    /**
     * Check if object is of accepted class.
     * @param object
     */
    protected function checkAccepted($object)
    {
        if (!$object instanceof $this->accepted) {
            throw new \InvalidArgumentException('Collection: Inserted object is not of the accepted class.');
        }
    }

    /**
     * Adds items to the end of array.
     * @param object[]
     */
    public function append($items)
    {
        foreach ($items as $item) {
            $this->checkAccepted($item);
        }
        parent::append($items);
    }

    /**
     * Adds items to the beginning of array. Does not preserve keys.
     * @param object[]
     */
    public function prepend($items)
    {
        foreach (array_reverse($items) as $item) {
            $this->checkAccepted($item);
        }
        parent::prepend($items);
    }

    /**
     * Insert items at given position. Does not preserve keys.
     * @param object[]
     * @param integer
     */
    public function insertAt($items, $position)
    {
        foreach ($items as $item) {
            $this->checkAccepted($item);
        }
        parent::insertAt($items, $position);
    }

    /**
     * ArrayAccess interface
     * @param integer
     * @param object
     */
    public function offsetSet($key, $value)
    {
        $this->checkAccepted($value);
        parent::offsetSet($key, $value);
    }

}
