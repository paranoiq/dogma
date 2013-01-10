<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;

use Dogma\Normalizer;
use Dogma\Model\EntityMapper;


require_once __DIR__ . '/exceptions.php';


class Connection extends \Nette\Database\Connection {


    /** @var SqlPreprocessor */
    private $preprocessor;

    /** @var EntityMapper */
    private $mapper;

    /** @var Normalizer */
    private $normalizer;

    /** @var Debugging\MysqlDebugger */
    private $debugger;


    /**
     * @param string
     * @param string
     * @param string
     * @param array
     * @param \Nette\Database\IReflection
     */
    public function __construct($dsn, $username = NULL, $password  = NULL, array $options = NULL, Debugging\MysqlDebugger $debugger = NULL) {
        if ($debugger) {
            $debugger->setConnection($this);
            $debugger->suspend('warnings');
            $this->debugger = $debugger;
        }

        if (isset($options['driverClass'])) {
            $driverClass = $options['driverClass'];
            unset($options['driverClass']);
        } else {
            $driverClass = NULL;
        }

        try {
            parent::__construct($dsn, $username, $password, $options, $driverClass);
            if ($debugger) $debugger->restore();

        } catch (\PDOException $e) {
            if ($debugger && $debugger->translateExceptions) {
                throw $debugger->translateException($e);
            } else {
                throw $e;
            }
        }

        $this->preprocessor = new SqlPreprocessor($this);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('Dogma\Database\Statement', array($this)));
    }


    /**
     * @param SimpleMapper
     */
    public function setMapper(EntityMapper $mapper) {
        $this->mapper = $mapper;
    }


    /**
     * @return EntityMapper
     */
    public function getMapper() {
        return $this->mapper;
    }


    /**
     * @param Normalizer
     */
    public function setNormalizer(Normalizer $normalizer) {
        $this->normalizer = $normalizer;
    }


    /**
     * @return Normalizer
     */
    public function getNormalizer() {
        return $this->normalizer;
    }


    /**
     * @param string
     * @param array
     * @return bool
     */
    public function queryArgs($statement, $params) {
        if ($this->preprocessor && (count($params) || strpos($statement, ':') !== FALSE))
            list($statement, $params) = $this->preprocessor->process($statement, $params);

        try {
			// work-arround for PHP bug #61900
			$dblib = $this->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'dblib';
			if ($dblib) {
				set_error_handler(function($severity, $message, $file, $line) {
					if (($severity & error_reporting()) === $severity) {
  						throw new \PDOException($message, 0/*, $severity, $file, $line*/);
					}
 					return FALSE;
				});
			}
            $result = $this->prepare($statement)->execute($params);

			if ($this->debugger && $this->debugger->checkWarnings) {
                $this->debugger->checkWarnings();
            }

			if ($dblib) restore_error_handler();
            return $result;

        } catch (\PDOException $e) {
			if ($dblib) restore_error_handler();

            if ($this->debugger && $this->debugger->translateExceptions) {
                throw $this->debugger->translateException($e, $statement, $params);
            } else {
                throw $e;
            }
        }
    }


    /**
     * @param  string statement
     * @param  mixed
     * @return array
     */
    public function fetchColumnAll($args) {
        $args = func_get_args();
        $res = $this->queryArgs(array_shift($args), $args);
        $cols = array();
        while ($col = $res->fetchColumn()) {
            $cols[] = $col;
        }
        return $cols;
    }


    /**
     * Creates selector for table.
     * @param  string
     * @return \Nette\Database\Table\Selection
     */
    public function table($table) {
        return new Table\Selection($table, $this);
    }


    /**
     * Alias for beginTransaction()
     * @return bool
     */
    public function begin() {
        return $this->beginTransaction();
    }


    // QueryComposer ---------------------------------------------------------------------------------------------------

    /*
    public function compose() {
        return new QueryComposer;
    }

    /*
    public function select() {

    }

    public function insert() {

    }

    public function update() {

    }

    public function replace() {

    }

    public function delete() {

    }

    public function call() {

    }

    public function show() {

    }

    public function create() {

    }
    */
}
