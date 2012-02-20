<?php

namespace Dogma\Data;

use Dogma\Filesystem\CsvFile;


class CsvDataSource extends AbstractDataSourceBase implements IDataSource {
    
    
    private $file;
    
    private $skipped = 0;
    
    private $used = 0;
    
    
    public function __construct($fileName) {
        $this->file = new CsvFile($fileName);
    }
    
    
    /**
     * Get list of columns
     * @return array
     */
    public function getColumns() {
        return $this->file->getColumns();
    }
    
    
    /**
     * Get distinct values
     * @param string
     * @return array
     */
    public function getDistinctValues($column) {
        ///
    }
    
    
    /**
     * Get an item
     * @return array|FALSE
     */
    public function fetch() {
        while ($row = $this->file->fetch()) {
            foreach ($this->filters as $filter) {
                if (!$filter($row)) continue 2;
            }
            
            if (!empty($this->offset) && $this->offset > $this->skipped++) {
                continue 2;
            }
            
            if (!empty($this->limit) && $this->limit < $this->used++) {
                return FALSE;
            }
            
            return $row;
        }
        
        return FALSE;
    }
    
}

