<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class FetchKeysIterator extends \IteratorIterator
{
    use \Dogma\StrictBehaviorMixin;

    /** @var int|string */
    private $keysKey;

    /** @var int|string */
    private $valuesKey;

    /**
     * @param mixed[]|\Traversable $traversable
     * @param string|null $keysKey
     * @param string|null $valuesKey
     */
    public function __construct($traversable, $keysKey, $valuesKey = null)
    {
        Check::traversable($traversable);

        if (is_array($traversable)) {
            $traversable = new ArrayIterator($traversable);
        }

        parent::__construct($traversable);

        $this->keysKey = $keysKey;
        $this->valuesKey = $valuesKey;
    }

    public function current()
    {
        if ($this->valuesKey === null) {
            return parent::current();
        } else {
            $value = parent::current();
            if (!is_array($value) && !$value instanceof \ArrayAccess) {
                throw new \Dogma\InvalidTypeException('array or ArrayAccess', $value);
            }
            return $value[$this->valuesKey];
        }
    }

    /**
     * @return string|int
     */
    public function key()
    {
        if ($this->keysKey === null) {
            return parent::key();
        } else {
            $value = parent::current();
            if (!is_array($value) && !$value instanceof \ArrayAccess) {
                throw new \Dogma\InvalidTypeException('array or ArrayAccess', $value);
            }
            return $value[$this->keysKey];
        }
    }

}
