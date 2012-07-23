<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database;

use Dogma\Model\SimpleMapper;


/**
 * query(), fetch?(), exec():
 * New preprocesor makes dibi-like syntax possible.
 * Question mark (?) is not required and *not allowed* in alternative syntax.
 * eg. $db->exec("UPDATE x SET y = ", $y, "WHERE z = ", $z);
 */
class Connection extends \Nette\Database\Connection {
    
    
    /** @var SqlPreprocessor */
    private $preprocessor;
    
    /** @var SimpleMapper */
    private $mapper;


    /**
     * @param string
     * @param string
     * @param string
     * @param array
     * @param \Nette\Database\IReflection
     */
    public function __construct($dsn, $username = NULL, $password  = NULL, array $options = NULL, \Nette\Database\IReflection $databaseReflection = NULL) {
        parent::__construct($dsn, $username, $password, $options, $databaseReflection);
        
        $this->preprocessor = new SqlPreprocessor($this);
        $this->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('Dogma\Database\Statement', array($this)));
    }


    /**
     * @param SimpleMapper
     */
    public function setMapper(SimpleMapper $mapper) {
        $this->mapper = $mapper;
    }


    /**
     * @return SimpleMapper
     */
    public function getMapper() {
        return $this->mapper;
    }


    /**
     * @param string
     * @param array
     * @return bool
     */
    public function queryArgs($statement, $params) {
        if ($this->preprocessor && (count($params) || strpos($statement, ':') !== FALSE))
            list($statement, $params) = $this->preprocessor->process($statement, $params);
        
        return $this->prepare($statement)->execute($params);
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

