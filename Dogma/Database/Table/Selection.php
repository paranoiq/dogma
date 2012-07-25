<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Table;

use Nette\Database\Table\ActiveRow;
use Nette;
use PDO;


class Selection extends \Nette\Database\Table\Selection {


    /** @var \Dogma\Database\Connection */
    protected $connection;


    /**
     * @param array
     * @return \Dogma\Model\ActiveEntity|ActiveRow
     */
    protected function map($row) {
        if ($mapper = $this->connection->getMapper()) {
            return $mapper->getInstance($this->name, $row);
        } else {
            return $row;
        }
    }
    
    
    /**
	 * Inserts row in a table.
	 * @param  array|\Traversable|Table\Selection
	 * @param  bool
	 * @return \Dogma\Model\ActiveEntity|ActiveRow|int|bool
	 */
	public function insert($data, $ignore = FALSE) {
		if ($data instanceof \Nette\Database\Table\Selection) {
			$data = $data->getSql();

		} elseif ($data instanceof \Traversable) {
			$data = iterator_to_array($data);
		}

		$return = $this->connection->query("INSERT " . ($ignore ? "IGNORE " : "") . "INTO $this->delimitedName", $data); // paranoiq

        if (!is_array($data)) {
            return $return->rowCount();
        }

        $this->checkReferenceNewKeys = TRUE;

        if (!isset($data[$this->primary]) && ($id = $this->connection->lastInsertId())) {
            $data[$this->primary] = $id;
            $row = $this->rows[$id] = new ActiveRow($data, $this); // paranoiq

        } else {
            $row = new ActiveRow($data, $this); // paranoiq
        }
        
        return $this->map($row); // paranoiq
	}
    
    
    /**
     * Returns row specified by primary key.
     * @param  mixed
     * @return ActiveRow or FALSE if there is no such row
     */
    public function get($key) {
        return $this->map(parent::get($key));
    }


    /** @return ActiveRow */
    public function current() {
        return $this->map(parent::current());
    }


    /**
     * Returns specified row.
     * @param  string row ID
     * @return ActiveRow or NULL if there is no such row
     */
    public function offsetGet($key) {
        return parent::offsetGet($key); // $this->map();
    }


    /**
     * Returns next row of result.
     * @return ActiveRow or FALSE if there is no row
     */
    public function fetch() {
        return $this->map(parent::fetch());
    }
    

    /**
     * @param array|Traversable|Table\Selection
     * @return int
     */
    public function replace($data) {
        if ($data instanceof \Nette\Database\Table\Selection) {
            $data = $data->getSql();

        } elseif ($data instanceof \Traversable) {
            $data = iterator_to_array($data);
        }

        $return = $this->connection->query("REPLACE INTO $this->delimitedName", $data);

        $this->rows = NULL;
        if (!is_array($data)) {
            return $return->rowCount();
        }
        
        return 0;
    }
    
}
