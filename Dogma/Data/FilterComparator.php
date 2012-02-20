<?php

namespace Dogma\Data;


// bude to vůbec někdy třeba?

class FilterComparator {
    
    ///
    
    
    /**
     * @param array(Filter)
     * @return bool
     */
    public function matchFilters($filters) {
        foreach ($filters as $filter) {
            if ($this->propertyName !== $filter->getPropertyName()) continue;
            if (!$this->match($filter)) return FALSE;
        }
        return TRUE;
    }
    
    
    public function matchFilter($filter) {
        if ($filter->operator === self::EQUAL)
            return $this->matchValue($filter->value);
        if ($this->operator === self::EQUAL)
            return $filter->matchValue($this->value);
        if ($filter->operator === self::NOT_EQUAL)
            return $this->operator !== self::EQUAL || $filter->value !== $this->value;
        if ($this->operator === self::NOT_EQUAL)
            return $filter->operator !== self::EQUAL || $filter->value !== $this->value;
        // if ($this->operator === self::ENUM && $filter->operator === self::ENUM)
        //    return (bool) array_intersect();
        /// ranges
        /// strings
        return TRUE;
    }
    
    
    protected function intersectsLower($value) {
        /// non scalar types
        
        switch ($this->operator) {
        case self::LOWER:
            return $value < $this->value;
        case self::GREATER:
            return $value > $this->value;
        case self::LOWER_EQUAL:
            return $value <= $this->value;
        case self::GREATER_EQUAL:
            return $value >= $this->value;
        case self::RANGE:
            return $value >= $this->value[0] && $value <= $this->value[1];
        case self::ENUM:
            return in_array($value, $this->value);
        }
        return TRUE;
    }
    
}
