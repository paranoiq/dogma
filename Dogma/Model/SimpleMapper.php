<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Model;


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
     * @param \Nette\Database\Table\ActiveRow
     * @return ActiveEntity
     */
    public function getInstance($table, $row) {
        if (array_key_exists($table, $this->map)) {
            $class = $this->map[$table];
            return new $class($row);
            
        } else {
            return new ActiveEntity($row);
        }
    }
    
}

