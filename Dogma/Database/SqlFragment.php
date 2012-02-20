<?php

namespace Dogma\Database;


class SqlFragment {
    
    /** @var string */
    public $statement;
    
    /** @var array */
    public $args = array();
    
    
    /**
     * SQL query Fragment.
     * @param  string  statement
     * @param  mixed   [parameters, ...]
     */
    public function __construct($statement) {
        $args = func_get_args();
        $this->statement = (string) array_shift($args);
        $this->args($args);
    }
    
}
