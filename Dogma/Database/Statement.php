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
    
    const FIELD_DATE = 'date',
        FIELD_SET = 'set';
    
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
        
        //return parent::normalizeRow($row);
        
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
                
            } elseif ($type === self::FIELD_DATE) {
                $row[$key] = new \Dogma\Date($value);
                
            }/* elseif ($type === self::FIELD_SET) {
                $row[$key] = new \Dogma\Database\Set($value);
            }*/
        }
        
        return $this->connection->getSupplementalDriver()->normalizeRow($row, $this);
    }
    
    
    private function detectColumnTypes() {
        if (!$this->connection->getSupplementalDriver()->supports['meta']) // workaround for PHP bugs #53782, #54695
            return array();
        
        $types = array();
        $col = 0;
        while ($meta = $this->getColumnMeta($col++)) {
            if (isset($meta['native_type'])) {
                $types[$meta['name']] = static::detectType($meta['native_type']);
            }
        }
        
        return $types;
    }
    
    
    /**
     * Heuristic type detection.
     * @param  string
     * @return string
     * @internal
     */
    public static function detectType($type) {
        static $types, $patterns = array(
            'BYTEA|BLOB|BIN' => IReflection::FIELD_BINARY,
            'TEXT|CHAR' => IReflection::FIELD_TEXT,
            'YEAR|BYTE|COUNTER|SERIAL|INT|LONG' => IReflection::FIELD_INTEGER,
            'CURRENCY|REAL|MONEY|FLOAT|DOUBLE|DECIMAL|NUMERIC|NUMBER' => IReflection::FIELD_FLOAT,
            'TIME' => IReflection::FIELD_DATETIME,
            'BOOL|BIT' => IReflection::FIELD_BOOL,
            'DATE' => self::FIELD_DATE,
            'SET' => self::FIELD_SET,
        );
        
        if (!isset($types[$type])) {
            $types[$type] = 'string';
            foreach ($patterns as $s => $val) {
                if (preg_match("#$s#i", $type)) {
                    return $types[$type] = $val;
                }
            }
        }
        return $types[$type];
    }
    
}

