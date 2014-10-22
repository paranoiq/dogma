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
 * Array object
 *
 * @todo: sjednotit chování $preserveKeys?
 * @todo: unique() - n^2 => n * log n
 */
class ArrayObject extends \Nette\Object implements \Countable, \IteratorAggregate, \ArrayAccess {

    /** Apply operation on yourself */
    const YOURSELF = true;


    /** @var array */
    protected $data = array();

    /** @var bool */
    public $preserveKeys = false;


    /**
     * @param array|Traversable
     */
    public function __construct($array = array()) {
        if (is_array($array)) {
            $this->data = $array;

        } elseif ($array instanceof ArrayObject) {
            $this->data = $array->toArray();

        } elseif ($array instanceof \Traversable) {
            $this->data = iterator_to_array($array, true);

        } else {
            throw new \InvalidArgumentException("ArrayObject: First parameter of constructor must be an array or traversable object.");
        }
    }


    /**
     * Set array to preserve-keys mode.
     * @param bool
     */
    public function setPreserveKeys($preserveKeys = true) {
        $this->preserveKeys = $preserveKeys;
    }


    /**
     * Returns array data.
     * @return array
     */
    public function toArray() {
        return $this->data;
    }


    /**
     * Get values of array (reindex)
     * @param bool
     * @return self|null
     */
    public function values($yourself = false) {
        if ($yourself) {
            $this->data = array_values($this->data);
            return null;
        } else {
            return new static(array_values($this->data));
        }
    }


    /**
     * Apply callback on each item. Calls: callback($item, $key, $param)
     * @param callback
     * @param mixed
     */
    public function each($callback, $param = null) {
        array_walk($this->data, $callback, $param);
    }


    /**
     * Search for given key.
     * @param string|integer
     * @return boolean
     */
    public function hasKey($key) {
        return array_key_exists($key, $this->data);
    }



    // filtering -------------------------------------------------------------------------------------------------------


    /**
     * Get slice of array by offset and limit. Allways preserves keys.
     * @param integer
     * @param integer
     * @param boolean
     * @return self
     */
    public function slice($offset, $limit, $yourself = false) {
        if ($yourself) {
            $this->data = array_slice($this->data, $offset, $limit, $this->preserveKeys);
            return $this;
        } else {
            return new static(array_slice($this->data, $offset, $limit, $this->preserveKeys));
        }
    }


    /**
     * Filter items in array by callback function. Allways preserves keys.
     * @param callback|Regexp|ICondition|array
     * @param boolean
     * @return self
     */
    public function filter($filter, $yourself = false) {
        if (is_callable($filter)) {
            // ok
        } elseif ($filter instanceof Regexp) {
            $filter = function ($value) use ($filter) { return $filter->match($value); };
        } elseif (is_array($filter) || $filter instanceof ArrayObject || $filter instanceof \Traversable) {
            $arr = new static($filter);
            $filter = function ($value) use ($arr) { return $arr->contains($value); };
        } else {
            throw new \InvalidArgumentException('ArrayObject: Filter must be a valid callback, Regexp, Condition or array.');
        }

        if ($yourself) {
            $this->data = array_filter($this->data, $filter);
            return $this;
        } else {
            return new static(array_filter($this->data, $filter));
        }
    }


    /**
     * Remove duplicit items from array. Allways preserves keys.
     * @param callback|Language\Collator|Regexp  optional
     * @param boolean
     * @return self
     */
    public function unique($collator = null, $yourself = null) {
        if (is_bool($collator) && is_null($yourself)) {
            $yourself = $collator;
            $collator = null;
        }

        /** @var \Dogma\ArrayObject $arr */
        $arr = new static;
        foreach ($this->data as $key => $value) { /// n^2
            if (!$arr->contains($value, $collator)) $arr[$key] = $value;
        }

        if ($yourself) {
            $this->data = $arr->toArray();
            return $this;
        } else {
            return $arr;
        }
    }


    // sorting ---------------------------------------------------------------------------------------------------------


    /**
     * Sort array using Collator, callback or standard sort function.
     * @param callback|Language\Collator|ISortCondition  (strcmp | strcasecmp | strnatcmp | strnatcasecmp | ...)
     */
    public function sort($collator = null) {
        if ($collator === null) {
            $this->preserveKeys ? asort($this->data) : sort($this->data);
        //} elseif ($collator instanceof Language\Collator) {
        //    $this->preserveKeys ? $collator->asort($this->data) : $collator->sort($this->data);

        } elseif (is_callable($collator)) {
            $this->preserveKeys ? uasort($this->data, $collator) : usort($this->data, $collator);

        } else {
            throw new \InvalidArgumentException("ArrayObject: invalid sort Collator or callback.");
        }
    }


    /**
     * Reverse the order of items.
     */
    public function reverse() {
        $this->data = array_reverse($this->data, $this->preserveKeys);
    }


    // items -----------------------------------------------------------------------------------------------------------


    /**
     * Search for item in array and return its key. Returns false if not found.
     * @param mixed
     * @param callback|Language\Collator|Regexp
     * @return mixed
     */
    public function find($item, $collator = null) {
        if ($collator === null) {
            return array_search($item, $this->data, true);

        } elseif ($collator instanceof Language\Collator) {
            foreach ($this->data as $key => $value) {
                if ($collator->compare($value, $item) === 0) return $key;
            }

        } elseif ($collator instanceof Regexp) {
            foreach ($this->data as $key => $value) {
                if ($collator->match($value)) return $key;
            }

        } elseif (is_callable($collator)) {
            foreach ($this->data as $key => $value) {
                if ($collator($value, $item)) return $key;
            }

        } else {
            throw new \InvalidArgumentException("ArrayObject: Invalid Collator or callback.");
        }

        return false;
    }


    /**
     * Whether the array contains the item.
     * @param mixed
     * @param callback|Language\Collator|Regexp
     * @return bool
     */
    public function contains($item, $collator = null) {
        return $this->search($item, $collator) === false ? false : true;
    }


    /**
     * Adds items to the end of array.
     * @param array
     */
    public function append($items) {
        foreach ($items as $item) {
            $this->offsetSet(null, $item);
        }
    }


    /**
     * Adds items to the beginning of array. Does not preserve keys.
     * @param array
     */
    public function prepend($items) {
        $items = $this->convertToArray($items);
        foreach (array_reverse($items) as $item) {
            array_unshift($this->data, $item);
        }
    }


    /**
     * Insert items at given position. Does not preserve keys.
     * @param array
     * @param integer
     */
    public function insertAt($items, $position) {
        $end = array_slice($this->data, $position, count($this->data) - $position);
        $this->data = array_slice($this->data, 0, $position, $this->preserveKeys);
        foreach ($items as $item) {
            $this->offsetSet(null, $item);
        }
        foreach ($end as $item) {
            $this->offsetSet(null, $item);
        }
    }


    /**
     * Removes and returns first item. Does not preserve keys.
     * @param mixed
     */
    public function removeFirst() {
        return array_shift($this->data);
    }


    /**
     * Removes and returns last item. Does not preserve keys.
     * @param mixed
     */
    public function removeLast() {
        return array_pop($this->data);
    }


    /**
     * Removes and returns item at given position. Allways preserves keys.
     * @param mixed
     */
    public function removeAt($position) {
        $buff = $this->data[$position];
        unset($this->data[$position]);
        return $buff;
    }


    // interfaces ------------------------------------------------------------------------------------------------------


    /**
     * Countable interface
     * @return integer
     */
    public function count() {
        return count($this->data);
    }


    /**
     * IteratorAggregate interface
     * @return \ArrayIterator
     */
    public function getIterator() {
        return new \ArrayIterator($this->data);
    }


    /**
     * ArrayAccess interface
     * @param int|string|null $key
     * @param mixed $value
     * @throws \InvalidArgumentException
     */
    public function offsetSet($key, $value) {
        if ($key === null) {
            $this->data[] = $value;

        } elseif (is_string($key)) {
            $this->data[$key] = $value;

        } elseif (is_integer($key)) {
            if ($key < 0)
                throw new \InvalidArgumentException("ArrayObject: Array index must a non-negative integer. '$key' given.");
            $this->data[$key] = $value;

        } else {
            throw new \InvalidArgumentException("ArrayObject: Array key must be a non-negative integer or a string. " . gettype($key) . " given.");
        }
    }

    /**
     * ArrayAccess interface
     * @param int|string|null $key
     * @return mixed
     */
    public function offsetGet($key) {
        return $this->data[$key];
    }

    /**
     * ArrayAccess interface
     * @param int|string|null $key
     * @return bool
     */
    public function offsetExists($key) {
        return array_key_exists($key, $this->data);
    }

    /**
     * ArrayAccess interface
     * @param int|string|null $key
     */
    public function offsetUnset($key) {
        unset($this->data[$key]);
    }


    // other -----------------------------------------------------------------------------------------------------------


    /**
     * Convert ArrayObject or Iterator to array.
     */
    private function convertToArray(&$object) {
        if (is_array($object)) {
            return $object;

        } elseif ($object instanceof ArrayObject) {
            return $object->toArray();

        } elseif ($object instanceof \Traversable) {
            return iterator_to_array($object, true);

        } else {
            throw new \InvalidArgumentException("ArrayObject: Cannot convert argument to array.");
        }
    }

}
