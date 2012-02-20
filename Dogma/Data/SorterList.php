<?php

namespace Dogma\Data;


class SorterList extends \Nette\Object implements ISorterList {
    
    protected $sorters = array();
    
    
    /**
     * @param array
     */
    public function __construct($sorters = array()) {
        foreach ($sorters as $sorter) {
            $this->addSorter($sorter);
        }
    }
    
    
    /**
     * Add a sorting condition to the list.
     * @param ISorter
     */
    public function addSorter(ISorter $sorter) {
        $this->sorters[] = $sorter;
    }
    
    
    
    
    public function __invoke($object1, $object2) {
        foreach ($this->sorters as $sorter) {
            $result = $sorter($object1, $object2);
            if ($result) return $result;
        }
        
        return 0;
    }
    
    
    public function getChildren() {
        return $this->sorters;
    }
    
}
