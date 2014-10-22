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
 * CSV file reader/writer
 *
 * @see http://tools.ietf.org/html/rfc4180
 *
 * @todo implement regexp validator?
 * @todo implement enum validator?
 */
final class CsvFile extends TextFile {

    const AUTODETECT = null;


    /** @var string */
    private $delimiter = ',';
    private $quoteChar = '"';
    private $escapeChar = '"';

    /** @var \Dogma\Normalizer */
    private $normalizer;


    /** @var integer[] (string $realName => integer $i) real column names */
    private $realColumns = array();
    /** @var mixed[][] (string $alias => mixed[] ($options)) user column options */
    private $columns = array();

    /** @var boolean autodetect types if no columns are defined? */
    private $autodetect = false;
    /** @var boolean autodetect includes null? */
    private $nullable = false;


    /** @var integer row counter (reading) */
    private $counter = 1;

    /** @var integer column count (writing) */
    private $columnCount;


    /**
     * Set CSV column delimiter
     * @param string|null for autodetect
     */
    public function setDelimiter($delimiter) {
        $this->delimiter = (string) $delimiter;
    }


    /**
     * Set formating options
     * @param string
     * @param string
     */
    public function setFormat($type, $format) {
        $this->getNormalizer()->setFormat($type, $format);
    }


    /**
     * Set type autodetection
     * @param boolean
     * @param boolean
     */
    public function autodetectTypes($autodetect = true, $nullable = false) {
        $this->autodetect = $autodetect;
        $this->nullable = $nullable;
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
     * @param string $name user column name
     * @param string $realName real column name in file
     * @param string $type column type (string|int|float|bool|date|datetime|bool)
     * @param boolean $required
     * @param boolean null value allowed?
     * @return self
     */
    public function addColumn($name, $realName = null, $type = Type::STRING, $required = true, $nullable = false) {
        $this->columns[$name] = Nette\ArrayHash::from(array(
            'realName' => $realName ?: $name,
            'type' => $type,
            'required' => $required,
            'nullable' => $nullable));

        return $this;
    }


    /**
     * Set required parameter for last inserted column
     * @param boolean
     * @return self
     */
    public function setRequired($required = true) {
        end($this->columns)->required = $required;

        return $this;
    }


    /**
     * Set nullable parameter for last inserted column
     * @param boolean
     * @return self
     */
    public function setNullable($nullable = true) {
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
     * @return boolean
     */
    public function hasColumn($name) {
        if (!$this->realColumns) $this->initializeRead();

        if ($this->columns) return isset($this->columns[$name]);

        return isset($this->realColumns[$name]);
    }


    /**
     * Returns list of columns
     * @return string[] (string $name => string $type)
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
                $columns[$name] = null;
            }
        }

        return $columns;
    }


    // Data access------------------------------------------------------------------------------------------------------


    /**
     * Skip n rows
     * @param integer
     * @return integer actually skipped
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
     * Returns next CSV row or false
     * @return array|boolean
     */
    public function fetch() {
        if (!$this->realColumns) $this->initializeRead();

        $row = $this->getNextRow();
        if (!$row) return false;

        if (!$this->columns)
            return $this->assocRow($row);

        return $this->normalizeRow($row);
    }


    /**
     * Returns next CSV row or false
     * @param string
     * @return mixed|boolean
     */
    public function fetchColumn($name) {
        if (!$this->realColumns) $this->initializeRead();

        $row = $this->getNextRow();
        if (!$row) return false;

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
     * @return mixed[]
     */
    private function getNextRow() {
        do {
            $this->counter++;
            $row = fgetcsv($this->file, 0, $this->delimiter, $this->quoteChar, $this->escapeChar);
            if (!$row) {
                return false;
                //throw new FileException("CsvFile: Error when reading a row from CSV file.");
            }

        } while ($row === array(null) && !$this->eof()); // skip empty rows

        if ($row === array(null)) return false; // eof

        if (count($row) !== count($this->realColumns))
            throw new FileException("CsvFile: Wrong column count on line #$this->counter.");

        return $row;
    }


    /**
     * Format row as associative array
     * @param mixed[]
     * @return mixed[]
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
     * @param mixed[]
     * @return mixed[]
     */
    private function normalizeRow(array $row) {
        $data = array();
        foreach ($this->columns as $name => $column) {
            if (!$column->required && !isset($this->realColumns[$column->realName])) {
                $data[$name] = isset($column['default']) ? $column['default'] : null;
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
     * @param boolean
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

                    $row[] = $this->getNormalizer()->format(null);
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
    }


    /**
     * Detect format of file
     * @param array
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
        $comma = count($row);

        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, ";", $this->quoteChar, $this->escapeChar);
        $semi = count($row);

        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, "\t", $this->quoteChar, $this->escapeChar);
        $tab = count($row);

        $this->setPosition(0);
        $row = fgetcsv($this->file, 0, "|", $this->quoteChar, $this->escapeChar);
        $pipe = count($row);

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
