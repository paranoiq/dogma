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


class Selection extends \Nette\Database\Table\Selection
{

    /** @var \Dogma\Database\Connection */
    protected $connection;


    /**
     * @param array
     * @return \Dogma\Model\ActiveEntity|ActiveRow
     */
    protected function map($row)
    {
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
    public function insert($data, $map = false)
    {
        $row = parent::insert($data);
        return $map ? $this->map($row) : $row;
    }


    /**
     * Inserts row in a table.
     * @param  array|\Traversable|Table\Selection
     * @param  bool
     * @return \Dogma\Model\ActiveEntity|ActiveRow|int|bool
     */
    public function insertIgnore($data, $map = false)
    {
        if ($data instanceof \Nette\Database\Table\Selection) {
            $data = $data->getSql();

        } elseif ($data instanceof \Traversable) {
            $data = iterator_to_array($data);
        }

        $return = $this->connection->query("INSERT IGNORE INTO $this->delimitedName", $data);

        if (!is_array($data)) {
            return $return->rowCount();
        }

        $this->checkReferenceNewKeys = true;

        if (!isset($data[$this->primary]) && ($id = $this->connection->lastInsertId())) {
            $data[$this->primary] = $id;
            $row = $this->rows[$id] = new ActiveRow($data, $this);

        } else {
            $row = new ActiveRow($data, $this);
        }

        return $map ? $this->map($row) : $row;
    }


    /**
     * @param array|Traversable|Table\Selection
     * @return int
     */
    public function replace($data)
    {
        if ($data instanceof \Nette\Database\Table\Selection) {
            $data = $data->getSql();

        } elseif ($data instanceof \Traversable) {
            $data = iterator_to_array($data);
        }

        $return = $this->connection->query("REPLACE INTO $this->delimitedName", $data);

        $this->rows = null;
        if (!is_array($data)) {
            return $return->rowCount();
        }

        return 0;
    }


    /**
     * Returns row specified by primary key.
     * @param  mixed
     * @return ActiveRow or false if there is no such row
     */
    public function get($key, $map = false)
    {
        $row = parent::get($key);
        return $map ? $this->map($row) : $row;
    }


    /** @return ActiveRow */
    public function current()
    {
        return $this->map(parent::current()); /// always map?
    }


    /**
     * Returns specified row.
     * @param  string
     * @return ActiveRow or null if there is no such row
     */
    public function offsetGet($key)
    {
        return parent::offsetGet($key); /// never map?
    }


    /**
     * Returns next row of result.
     * @return ActiveRow or false if there is no row
     */
    public function fetch($map = false)
    {
        $row = parent::fetch();
        return $map ? $this->map($row) : $row;
    }

}
