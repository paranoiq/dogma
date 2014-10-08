<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

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
