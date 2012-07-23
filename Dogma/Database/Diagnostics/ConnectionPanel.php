<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Diagnostics;


class ConnectionPanel extends \Nette\Database\Diagnostics\ConnectionPanel {
    
    public $maxQueries = 100;
    
    private $counter = 0;
    
    
    public function logQuery(\Nette\Database\Statement $result, array $params = NULL) {
        $this->counter++;
        if ($this->counter > $this->maxQueries) return;
        
        parent::logQuery($result, $params);
    }
    
}
