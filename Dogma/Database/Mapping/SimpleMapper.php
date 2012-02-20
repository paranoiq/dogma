<?php

namespace Dogma\Database\Mapping;


class SimpleMapper extends \Nette\Object {
    
    /** @var array table => class map */
	protected $map;
	
	
	/**
     * Set mapping of tables to classes (descendants of ActiveRow)
     * @param array (table => class)
     */
    public function __construct(array $map) {
        $this->map = $map;
    }
    
	
	/**
     * Translate table name to class name
     * @param string
     */
    public function mapTable($table) {
        if (array_key_exists($table, $this->map)) {
            return $this->map[$table];
        }
    }
    
}

