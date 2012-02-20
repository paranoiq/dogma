<?php

namespace Dogma\Database\Table;

use Nette;
use PDO;


class Selection extends \Nette\Database\Table\Selection {
    
    
    protected function instantiate($data) {
        if ($mapper = $this->connection->getMapper()) {
            $class = $mapper->mapTable($this->name);
        }
        
        if (empty($class)) $class = 'Dogma\\Database\\Table\\ActiveRow';
        return new $class($data, $this);
    }
    
    
    /**
	 * Executes built query.
	 * @return NULL
	 */
	protected function execute()
	{
		if ($this->rows !== NULL) {
			return;
		}

		try {
			$result = $this->query($this->getSql());

		} catch (\PDOException $exception) {
			if (!$this->select && $this->prevAccessed) {
				$this->prevAccessed = '';
				$this->accessed = array();
				$result = $this->query($this->getSql());
			} else {
				throw $exception;
			}
		}

		$this->rows = array();
		$result->setFetchMode(PDO::FETCH_ASSOC);
		foreach ($result as $key => $row) {
			$row = $result->normalizeRow($row);
			$this->rows[isset($row[$this->primary]) ? $row[$this->primary] : $key] = $this->instantiate($row); /// paranoiq
		}
		$this->data = $this->rows;

		if (isset($row[$this->primary]) && !is_string($this->accessed)) {
			$this->accessed[$this->primary] = TRUE;
		}
	}
    
    
    /**
	 * Inserts row in a table.
	 * @param  mixed array($column => $value)|Traversable for single row insert or TableSelection|string for INSERT ... SELECT
	 * @param  bool	 
	 * @return ActiveRow or FALSE in case of an error or number of affected rows for INSERT ... SELECT
	 */
	public function insert($data, $ignore = FALSE)
	{
		if ($data instanceof Selection) {
			$data = $data->getSql();

		} elseif ($data instanceof \Traversable) {
			$data = iterator_to_array($data);
		}

		$return = $this->connection->query("INSERT " . ($ignore ? "IGNORE " : "") . "INTO $this->delimitedName", $data);

		$this->rows = NULL;
		if (!is_array($data)) {
			return $return->rowCount();
		}

		if (!isset($data[$this->primary]) && ($id = $this->connection->lastInsertId())) {
			$data[$this->primary] = $id;
		}
		return $this->instantiate($data); /// paranoiq
	}
    
    
    /**
	 * Returns referencing rows.
	 * @param  string table name
	 * @return GroupedSelection
	 */
	public function getReferencingTable($table)
	{
		$column = $this->connection->databaseReflection->getReferencingColumn($table, $this->name);
		$referencing = new GroupedSelection($table, $this, $column);
		$referencing->where("$table.$column", array_keys((array) $this->rows)); // (array) - is NULL after insert
		return $referencing;
	}
	
    
    /**
     * Why the fuck is this private?!
     */
    protected function getPrimary($table) {
        return $this->connection->databaseReflection->getPrimary($table);
    }
    
}
