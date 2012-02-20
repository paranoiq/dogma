<?php

namespace Dogma\Data;

use Dogma\Object\PropertyAccessor;


/**
 * Condition to determine order of objects. Callback.
 */
class Sorter extends \Nette\Object implements ISorter {
    
    const ASC  =  1;
    const DESC = -1;
    
    /** @var string */
    protected $propertyName;
    
    /** @var int */
    protected $direction;
    
    /** @var callback|Language\Collator */
    protected $collator;
    
    
    /**
     * @param string
     * @param int
     * @param callback|Language\Collator
     */
    public function __construct($propertyName, $direction = self::ASC, $collator = NULL) {
        if ($direction !== self::ASC && $direction !== self::DESC)
            throw new \InvalidArgumentException("Sorter: Direction must be either 1 or -1.");
        
        $this->propertyName = $propertyName;
        $this->direction = $direction;
        $this->collator = $collator;
    }
    
    
    public function __invoke($object1, $object2) {
        $val1 = PropertyAccessor::getValue($object1, $this->propertyName);
        $val2 = PropertyAccessor::getValue($object2, $this->propertyName);
        
        if ($this->collator === NULL) {
            $order = ($val1 > $val2) ? 1 : (($val2 > $val1) ? -1 : 0);
            
        //} elseif ($this->collator instanceof Language\Collator) {
        //    $order = $this->collator->compare($val1, $val2);
            
        } elseif (is_callable($this->collator)) {
            $order = $this->collator($val1, $val2);
            
        } else {
            throw new \Exception("Sorter: invalid sort Collator or callback.");
        }
        
        return $order * $this->direction;
    }
    
    
    public function getPropertyName() {
        return $this->propertyName;
    }
    
    
    public function getDirection() {
        return $this->direction;
    }
    
}
