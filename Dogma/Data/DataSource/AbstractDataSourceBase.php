<?php

namespace Dogma\Data;


abstract class AbstractDataSourceBase extends \Nette\Object {
    
    
    protected $filters = array();
    
    protected $sorters = array();
    
    protected $limit;
    
    protected $offset;
    
    
    /**
     * Add filtering onto specified column
     * @param IFilter
     * @return IDataSource
     */
    public function addFilter(IFilter $filter) {
        $this->filters[] = $filter;
        
        return $this;
    }
    
    
    /**
     * Adds ordering to specified column
     * @param ISorter
     * @return IDataSource
     */
    function addSorter(ISorter $sorter) {
        $this->sorters[] = $sorter;
        
        return $this;
    }
    
    
    /**
     * Reduce the result starting from $start to have $count rows
     * @param int the number of results to obtain
     * @param int the offset
     * @throws \OutOfRangeException
     * @return IDataSource
     */
    function limit($limit, $offset = 0) {
        $limit = (int) $limit;
        if ($limit < 0)
            throw new \InvalidArgumentException("DataSource: Limit cannot be a negative number.");
            
        $offset = (int) $offset;
        if ($offset < 0)
            throw new \InvalidArgumentException("DataSource: Offset cannot be a negative number.");
        
        
        $this->limit = $limit;
        $this->offset = $offset;
        
        return $this;
    }
    
}
