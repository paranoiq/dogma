<?php

namespace Dogma\Data;


class FilterList extends \Nette\Object implements IFiilterList {
    
    protected $operator = array();
    
    protected $negative;
    
    protected $filters = array();
    
    
    /**
     * @param string
     * @param array
     */
    public function __construct($operator, $filters = array()) {
        $this->operator = $operator;
        
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }
    
    
    /**
     * Add a filter to the list.
     * @param IFilter
     */
    public function addFilter(IFilter $filter) {
        $this->filters[] = $filter;
    }
    
    
    
    
    public function __invoke($object) {
        foreach ($this->filters as $filter) {
            $result = $filter($object);
            if (!$result) return $result;
        }
        
        return TRUE;
    }
    
    
    public function getOperator() {
        return $this->operator;
    }
    
    
    public function isNegative() {
        return $this->negative;
    }
    
    
    public function getChildren() {
        return $this->filters;
    }
    
}
