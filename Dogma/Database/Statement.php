<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;

use Nette\Database\IReflection;


class Statement extends \Nette\Database\Statement {


    /** @var Connection */
    private $connection;

    /** @var array */
    private $types;


    protected function __construct(Connection $connection) {
        parent::__construct($connection);
        $this->connection = $connection;
        $this->setFetchMode(\PDO::FETCH_CLASS, 'Nette\Database\Row', array($this));
    }


    /**
     * Normalizes result row.
     * @param  array
     * @return array
     */
    public function normalizeRow($row) {
        if ($this->types === NULL)
            $this->types = $this->detectColumnTypes();

        // convert DATETIME, DATE and SET
        foreach ($this->types as $key => $type) {
            $value = $row[$key];
            if ($value === NULL || $value === FALSE || $type === IReflection::FIELD_TEXT) {

            } elseif ($type === IReflection::FIELD_INTEGER) {
                $row[$key] = is_float($tmp = $value * 1) ? $value : $tmp;

            } elseif ($type === IReflection::FIELD_FLOAT) {
                $row[$key] = (string) ($tmp = (float) $value) === $value ? $tmp : $value;

            } elseif ($type === IReflection::FIELD_BOOL) {
                $row[$key] = ((bool) $value) && $value !== 'f' && $value !== 'F';

            } elseif ($type === IReflection::FIELD_DATETIME) {
                $row[$key] = new \Dogma\DateTime($value);

            } elseif ($type === IReflection::FIELD_DATE) {
                $row[$key] = new \Dogma\Date($value);

            }
        }

        // GROUP_CONCAT(...) as `column[]`
        foreach ($row as $key => $value) {
            if (substr($key, -2) === '[]') {
                $vals = explode(',', $value);
                if ($normalizer = $this->connection->getNormalizer()) {
                    foreach ($vals as &$val) {
                        $val = $normalizer->autodetect($val);
                    }
                }
                $row[substr($key, 0, -2)] = $vals;
                unset($row[$key]);
            }
        }

        return $this->connection->getSupplementalDriver()->normalizeRow($row, $this);
    }


    /**
     * Returns count of rows in result
     * @return int
     */
    public function count() {
        return parent::rowCount();
    }


    /**
     * @param  mixed
     * @return array
     */
    public function fetchColumnAll() {
        $cols = array();
        while ($col = $this->fetchColumn()) {
            $cols[] = $col;
        }
        return $cols;
    }

}
