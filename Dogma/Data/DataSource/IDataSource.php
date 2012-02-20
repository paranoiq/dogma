<?php

namespace Dogma\Data;


interface IDataSource {
    
    
    /**
     * Get list of columns
     * @return array
     */
    function getColumns();
    
    
    /**
     * Get distinct values
     * @param string
     * @return array
     */
    function getDistinctValues($column);
    
    
    /**
     * Add filtering onto specified column
     * @param IFilter
     * @return IDataSource
     */
    function addFilter(IFilter $filter);
    
    
    /**
     * Adds ordering to specified column
     * @param ISorter
     * @return IDataSource
     */
    function addSorter(ISorter $sorter);
    
    
    /**
     * Reduce the result starting from $start to have $count rows
     * @param int the number of results to obtain
     * @param int the offset
     * @throws \OutOfRangeException
     * @return IDataSource
     */
    function limit($limit, $offset = 0);
    
    
    /**
     * Get an item
     * @return array
     */
    function fetch();
    
    
    /* function count(); */
}
