<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;

use Nette\Utils\Strings;
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;


class SqlPreprocessor {


    /** @var Connection */
    private $connection;

    /** @var \Nette\Database\ISupplementalDriver */
    private $driver;

    /** @var string */
    private $sql;

    /** @var array of parameters to be processed by PDO */
    private $remaining;



    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->driver = $connection->getSupplementalDriver();
    }


    /**
     * Format parameters and compile the query.
     * @param string
     * @param mixed
     * @return array
     */
    public function process($sql, $params) {

        $this->sql = '';
        $this->remaining = [];

        $sql2 = Strings::replace($sql, '~\'.*?\'|".*?"|\?~s', [$this, 'splitCb']);
        if (strpos($sql2, chr(0)) !== false) { // placeholder mode
            $args = $params;
            $bits = explode(chr(0), $sql2);

        } else { // alternate mode
            $args = [];
            $bits[0] = $sql;
            foreach ($params as $param) {
                if (count($bits) > count($args)) {
                    $args[] = $param;
                } else {
                    $bits[] = $param;
                }
            }
        }

        foreach ($bits as $i => $sql) {
            $this->sql .= $sql;
            if (!array_key_exists($i, $args)) continue;

            $this->processArg($args[$i], $sql);
        }

        //$this->sql = Strings::replace($this->sql, '~\'.*?\'|".*?"|:[a-zA-Z0-9_]+:~s', [$this, 'substituteCb']);

        return [$this->sql, $this->remaining];
    }


    /** @internal */
    public function splitCb($m) {
        $m = $m[0];
        if ($m[0] === "'" || $m[0] === '"') { // string
            return $m;

        } elseif ($m[0] === '?') { // placeholder
            return chr(0);

        } else {
            die;
        }
    }


    //** @internal */
    /*public function substituteCb($m) {
        $m = $m[0];
        if ($m[0] === "'" || $m[0] === '"') { // string
            return $m;

        } elseif ($m[0] === ':') { // substitution
            $s = substr($m, 1, -1);
            return isset($this->connection->substitutions[$s]) ? $this->connection->substitutions[$s] : $m;
        }
    }*/


    /**
     * Format each argument depending on its context.
     * @param mixed
     * @param string
     */
    private function processArg($arg, $sql) {
        if ($arg instanceof \Dogma\SimpleValueObject || $arg instanceof ActiveRow) {
            $this->sql .= $this->formatValue($arg);

        } elseif ((is_array($arg) || $arg instanceof \Traversable) && $mode = $this->detectArrayMode($sql)) {
            $this->processArray($arg, $mode);

        } elseif (Strings::match($sql, '/\\s(?:LIMIT|OFFSET|TOP)\\s*$/i')) {
            $this->sql .= (int) $arg;

        } else {
            $this->sql .= $this->formatValue($arg);
        }
    }


    /**
     * Detect array mode from previous part of statement.
     * @param string
     * @return string
     */
    private function detectArrayMode($sql) {
        $sql = strtoupper($sql);

        if (Strings::match($sql, '/(?:SET|UPDATE)\\s*$/')) {
            return 'assoc';

        } elseif (Strings::match($sql, '/\\s(?:WHERE|HAVING|AND|&&|OR|\\|\\||XOR|NOT|!)[(\\s]*$/')) {
            return 'where';

        } elseif (Strings::match($sql, '/\\s(?:ANY|SOME|ALL|IN)\\s*$/')) {
            return 'in';

        } elseif (Strings::match($sql, '/\\s(?:ORDER\\sBY|GROUP\\sBY)\\s*$/')) {
            return 'order';

        } elseif (Strings::match($sql, '/INSERT|REPLACE/')) {
            return 'insert';

        } else {
            return '';
        }
    }


    /**
     * Process array argument.
     * @param array
     * @param string
     */
    private function processArray($array, $mode) {
        $vx = [];

        if ($mode === 'insert') { // (key, key, ...) VALUES (value, value, ...)
            $this->processInsert($array);

        } elseif ($mode === 'values') { // , (value, value, ...)
            $this->sql .= ', (' . $this->formatValue($array) . ')';

        } elseif ($mode === 'in') { // (value, value, ...)
            $this->sql .= '(' . $this->formatValue($array) . ')';

        } elseif ($mode === 'assoc') { // key=value, key=value, ...
            foreach ($array as $k => $v) {
                $vx[] = $this->driver->delimite($k) . ' = ' . $this->formatValue($v);
            }
            $this->sql .= implode(', ', $vx);

        } elseif ($mode === 'order') { // key, key DESC, ...
            foreach ($array as $k => $v) {
                $desc = $v === false || (is_numeric($v) && $v <= 0);
                $vx[] = $this->driver->delimite($k) . ($desc ? ' DESC' : '');
            }
            $this->sql .= implode(', ', $vx);

        } elseif ($mode === 'where') { // key=value AND key=value AND ...
            $this->processWhere($array);
        }
    }


    /**
     * @param array
     */
    private function processInsert($array) {
        $vx = $kx = [];

        // multiinsert?
        reset($array);
        if (current($array) instanceof \Dogma\SimpleValueObject || current($array) instanceof ActiveRow) {
            //
        } elseif (is_array(current($array)) || current($array) instanceof \Traversable) {
            $all = $array;
            $array = array_shift($all);
        }

        foreach ($array as $k => $v) {
            $kx[] = $this->driver->delimite($k);
            $vx[] = $this->formatValue($v);
        }
        $this->sql .= '(' . implode(', ', $kx) . ') VALUES (' . implode(', ', $vx) . ')';

        if (isset($all)) {
            foreach ($all as $array) {
                $this->processArray($array, 'values');
            }
        }
    }


    /**
     * @param array
     */
    private function processWhere($array) {
        $vx = [];

        foreach ($array as $k => $v) {
            if (is_string($v)) {
                $vx[] = $this->driver->delimite($k) . ' LIKE ' . $this->formatValue($v);

            } elseif (is_null($v)) {
                $vx[] = $this->driver->delimite($k) . ' IS NULL';

            // MySQL 5+, PostgreSQL
            } elseif (is_bool($v)) {
                $vx[] = $this->driver->delimite($k) . ($v ? ' IS TRUE' : ' IS FALSE');

            } elseif (is_array($v) || $v instanceof \Traversable) {
                $vx[] = $this->driver->delimite($k) . ' IN (' . $this->formatValue($v) . ')';

            } else {
                $vx[] = $this->driver->delimite($k) . ' = ' . $this->formatValue($v);
            }
        }
        $this->sql .= '(' . implode(' AND ', $vx) . ')';
    }


    /**
     * Format a value for use in statement.
     * @param mixed
     * @return string
     */
    private function formatValue($value) {

        if (is_string($value)) {
            if (strlen($value) > 20) {
                $this->remaining[] = $value;
                return '?';

            } else {
                return $this->connection->quote($value);
            }

        } elseif (is_int($value)) {
            return (string) $value;

        } elseif (is_float($value)) {
            return rtrim(rtrim(number_format($value, 15, '.', ''), '0'), '.');

        } elseif (is_bool($value)) {
            $this->remaining[] = $value;
            return '?';

        } elseif (is_null($value)) {
            return 'NULL';

        } elseif ($value instanceof \DateTime) {
            return $this->driver->formatDateTime($value);

        } elseif ($value instanceof SqlLiteral || $value instanceof ActiveRow
            || $value instanceof \Dogma\Model\ActiveEntity
            || $value instanceof \Dogma\SimpleValueObject) {
            return $this->connection->quote((string) $value);

        } elseif ($value instanceof SqlFragment) {
            $pre = new static($this->connection);
            list($sql, $remaining) = $pre->process($value->statement, $value->params);

            if ($remaining) foreach ($remaining as $val) {
                $this->remaining[] = $val;
            }
            return $sql;

        } elseif (is_array($value) || $value instanceof \Traversable) {
            // non-associative; value, value, value
            $vx = [];
            foreach ($value as $v) {
                $vx[] = $this->formatValue($v);
            }
            if (!$vx) return 'NULL'; // empty array
            return implode(', ', $vx);

        } elseif (is_object($value)) {
            throw new DatabaseException("Unsupported parameter type: " . get_class($value) . ".");

        } else {
            $this->remaining[] = $value;
            return '?';
        }
    }

}
