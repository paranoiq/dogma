<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Nette;
use Dogma\Type;


/**
 * CSV file reader/Writer
 * 
 * @see http://tools.ietf.org/html/rfc4180
 * 
 * @todo implement regexp validator?
 * @todo implement enum validator?
 */
final class CsvFile extends TextFile {
    
    const AUTODETECT = NULL;
    
    
    /** @var string */
    private $delimiter = ',';
    private $quoteChar = '"';
    private $escapeChar = '"';
    
    /** @var \Dogma\Normalizer */
    private $normalizer;
    
    
    /** @var array(realName=>i) real column names */
    private $realColumns = array();
    /** @var array(alias=>array(options)) user column options */
    private $columns = array();
    
    /** @var bool autodetect types if no columns are defined? */
    private $autodetect = FALSE;
    /** @var bool autodetect includes null? */
    private $nullable = FALSE;
    
    
    /** @var int row counter (reading) */
    private $counter = 1;
    
    /** @var int column count (writing) */
    private $columnCount;
    
    
    /**
     * Set CSV column delimiter
     * @param string or NULL for autodetect
     * @return CsvFile
     */
    public function setDelimiter($delimiter) {
        $this->delimiter = (string) $delimiter;
        
        return $this;
    }


    /**
     * Set formating options
     * @param string
     * @param string
     * @return self
     */
    public function setFormat($type, $format) {
        $this->getNormalizer()->setFormat($type, $format);
        
        return $this;
    }


    /**
     * Set type autodetection
     * @param bool
     * @param bool
     * @return self
     */
    public function autodetectTypes($autodetect = TRUE, $nullable = FALSE) {
        $this->autodetect = $autodetect;
        $this->nullable = $nullable;
        
        return $this;
    }
    
    
    /**
     * @return \Dogma\Normalizer
     */
    private function getNormalizer() {
        if (!$this->normalizer) $this->normalizer = new \Dogma\Normalizer;
        
        return $this->normalizer;
    }
    
    
    // Column handling -------------------------------------------------------------------------------------------------
    
    
    /**
     * Add a column
     * @param string user column name
     * @param string real column name in file
     * @param string column type (string|int|float|bool|date|datetime|bool)
     * @param bool required?
     * @param bool NULL value allowed?
     * @return self
     */
    public function addColumn($name, $realName = NULL, $type = Type::STRING, $required = TRUE, $nullable = FALSE) {
        $this->columns[$name] = Nette\ArrayHash::from(array(
            'realName' => $realName ?: $name,
            'type' => $type,
            'required' => $required,
            'nullable' => $nullable));
        
        return $this;
    }


    /**
     * Set required parameter for last inserted column
     * @param bool
     * @return self
     */
    public function setRequired($required = TRUE) {
        end($this->columns)->required = $required;
        
        return $this;
    }


    /**
     * Set nullable parameter for last inserted column
     * @param bool
     * @return self
     */
    public function setNullable($nullable = TRUE) {
        end($this->columns)->nullable = $nullable;
        
        return $this;
    }
    
    
    /**
     * Set default value for last inserted column
     * @param mixed
     * @return self
     */
    public function setDefault($default) {
        end($this->columns)->default = $default;
        
        return $this;
    }
    
    
    /**
     * Check if file has column
     * @param string
     * @return bool
     */
    public function hasColumn($name) {
        if (!$this->realColumns) $this->initializeRead();
        
        if ($this->columns) return isset($this->columns[$name]);
        
        return isset($this->realColumns[$name]);
    }
    
    
    /**
     * Returns list of columns
     * @return array(name=>type)
     */
    public function getColumns() {
        if (!$this->realColumns) $this->initializeRead();
        $columns = array();
        
        if ($this->columns) {
            foreach ($this->columns as $name => $column) {
                $columns[$name] = $column->type;
            }
        } else {
            foreach ($this->realColumns as $name => $i) {
                $columns[$name] = NULL;
            }
        }
        
        return $columns;
    }
    
    
    // Data access------------------------------------------------------------------------------------------------------


    /**
     * Skip n rows
     * @param int
     * @return int actually skipped
     */
    public function skip($rows = 1) {
        $rows = (int) $rows;
        if ($rows < 0)
            throw new FileException("CsvFile: Cannot skip negative number of rows.");

        $skipped = 0;
        while ($this->getNextRow() && --$rows) {
            $skipped++;
        }

        return $skipped;
    }
    
    
    /**
     * Returns next CSV row or FALSE
     * @return array|FALSE
     */
    public function fetch() {
        if (!$this->realColumns) $this->initializeRead();
        
        $row = $this->getNextRow();
        if (!$row) return FALSE;
        
        if (!$this->columns)
            return $this->assocRow($row);
        
        return $this->normalizeRow($row);
    }
    
    
    /**
     * Returns next CSV row or FALSE
     * @param string
     * @return mixed|FALSE
     */
    public function fetchColumn($name) {
        if (!$this->realColumns) $this->initializeRead();
        
        $row = $this->getNextRow();
        if (!$row) return FALSE;
        
        if (!$this->columns) {
            if (isset($this->realColumns[$name]))
                throw new FileException("CsvFile: Column $name was not found.");
            
            return $row[$this->realColumns[$name]];
        }
        
        if (isset($this->columns[$name]))
            throw new FileException("CsvFile: Column $name was not found.");
        
        $column = $this->columns[$name];
        
        return $this->getNormalizer()->normalize(
            $this->decode($row[$this->realColumns[$column->realName]]), $column->type, $column->nullable);
    }
    
    
    /**
     * Get next row
     * @return array
     */
    private function getNextRow() {
        do {
            $this->counter++;
            $row = fgetcsv($this->file, 0, $this->delimiter, $this->quoteChar, $this->escapeChar);
            if (!$row) {
                return FALSE;
                //throw new FileException("CsvFile: Error when reading a row from CSV file.");
            }
            
        } while ($row === array(NULL) && !$this->eof()); // skip empty rows
        
        if ($row === array(NULL)) return FALSE; // eof
        
        if (count($row) !== count($this->realColumns))
            throw new FileException("CsvFile: Wrong column count on line #$this->counter.");
        
        return $row;
    }


    /**
     * Format row as associative array
     * @param array
     * @return array
     */
    private function assocRow(array $row) {
        $data = array();
        foreach ($this->realColumns as $realName => $i) {
            if ($this->autodetect) {
                $data[$realName] = $this->getNormalizer()->autodetect($this->decode($row[$i]));
            } else {
                $data[$realName] = $this->decode($row[$i]);
            }
        }
        return $data;
    }


    /**
     * Normalize row
     * @param array
     * @return array
     */
    private function normalizeRow(array $row) {
        $data = array();
        foreach ($this->columns as $name => $column) {
            if (!$column->required && !isset($this->realColumns[$column->realName])) {
                $data[$name] = isset($column['default']) ? $column['default'] : NULL;
                continue;
            }
            
            $columnId = $this->realColumns[$column->realName];
            $data[$name] = $this->getNormalizer()->normalize(
                $this->decode($row[$columnId]), $column->type, $column->nullable);
        }
        return $data;
    }


    /**
     * Write data to CSV file
     * @param array
     * @param bool
     * @return CsvFile     
     */
    public function writeData($data) {
        if (!$this->columnCount) {
            $this->initializeWrite($data);
        }
        
        $row = array();
        
        if ($this->columns) {
            foreach ($this->columns as $name => $column) {
                if (!array_key_exists($name, $data)) {
                    if ($column->required)
                        throw new FileException("CsvFile: Required value $name is missing.");
                    
                    $row[] = $this->getNormalizer()->format(NULL);
                } else {
                    $row[] = $this->encode(
                        $this->getNormalizer()->format($data[$name], $column->type, $column->nullable));
                }
            }
            
        } else {
            if (count($data) !== $this->columnCount)
                throw new FileException("CsvFile: Data count does not match column count.");
            
            foreach ($data as $value) {
                $row[] = $this->encode($this->getNormalizer()->format($value));
            }
        }
        
        return $this;
    }
    
    
    /**
     * Detect format of file
     * @param bool detect delimiter?
     * @param bool detect column names?
     */
    private function initializeWrite($data) {
        if (!$this->delimiter)
            throw new FileException("CsvFile: Delimiter must be set!");
        
        $this->columnCount = count($data);
        $row = array();
        
        // header from definition
        if ($this->columns) {
            foreach ($this->columns as $column) {
                $row[] = $this->encode($column['realName']);
            }
            
        // header from indexes
        } elseif (array_keys($data) !== range(0, count($data) - 1)) {
            foreach ($data as $name => $v) {
                $row[] = $this->encode($name);
            }
        
        // first row is header
        } else {
            return;
        }
        
        if (!fputcsv($this->file, $row, $this->delimiter, $this->quoteChar))
            throw new FileException("CsvFile: Error when writing file header.");
    }
    
    
    /**
     * Detect format of file
     * @param bool detect delimiter?
     * @param bool detect column names?
     */
    private function initializeRead() {
        if (!$this->delimiter) $this->detectDelimiter();
        
        $this->detectRealColumns();
        
        if ($this->columns) $this->checkRequiredColumns();
        
        $this->counter = 1;
    }
    
    
    /**
     * Detect delimiter from fist row of file. Detects [,] [;] [|] and [tab]
     */
    private function detectDelimiter() {
        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, ",", $this->quoteChar, $this->escapeChar);
        $comma = is_array($row) ? count($row) : 0;
        
        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, ";", $this->quoteChar, $this->escapeChar);
        $semi = is_array($row) ? count($row) : 0;
        
        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, "\t", $this->quoteChar, $this->escapeChar);
        $tab = is_array($row) ? count($row) : 0;
        
        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, "|", $this->quoteChar, $this->escapeChar);
        $pipe = is_array($row) ? count($row) : 0;
        
        if ($comma && $comma > $semi && $comma > $tab && $comma > $pipe) {
            $this->delimiter = ",";
            return;
            
        } elseif ($semi && $semi > $comma && $semi > $tab && $semi > $pipe) {
            $this->delimiter = ";";
            return;
            
        } elseif ($tab && $tab > $comma && $tab > $semi && $tab > $pipe) {
            $this->delimiter = "\t";
            return;
            
        } elseif ($pipe && $pipe > $comma && $pipe > $semi && $pipe > $tab) {
            $this->delimiter = "|";
            return;
            
        } else {
            throw new FileException("CsvFile: Cannot detect CSV delimiter.");
        }
    }
    
    
    /**
     * Detect real column names from file
     */
    private function detectRealColumns() {
        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, $this->delimiter, $this->quoteChar, $this->escapeChar);
        if (!$row)
            throw new FileException("CsvFile: Error when reading CSV file header.");
        
        $this->realColumns = array();
        foreach ($row as $i => $name) {
            $this->realColumns[$this->decode($name)] = $i;
        }
    }
    
    
    /**
     * Check if all required columns are present
     */
    private function checkRequiredColumns() {
        foreach ($this->columns as $name => $column) {
            if ($column->required && !isset($this->realColumns[$column->realName]))
                throw new FileException("CsvFile: Required column $name was not fund.");
        }
    }
    
}

