<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Diagnostics;

use Dogma\Database\Connection;
use Nette\Diagnostics\Debugger;


class ConnectionPanelLogQueryLimiter {
    
    /** @var int  maximum queries to log */
    public static $maxQueries = 100;
    
    private $count = 0;
    
    private $connection;
    
    
    public static function initialize(Connection $connection) {
        $limiter = new static;
        $limiter->connection = $connection;
        if (!Debugger::$productionMode) {
            $connection->onQuery[] = callback($limiter, 'checkPanel');
        }
    }
    
    
    public function checkPanel() {
        if (++$this->count < self::$maxQueries) return;
        
        foreach ($this->connection->onQuery as $i => $callback) {
            if ($callback instanceof \Nette\Callback) {
                $callback = $callback->getNative();
            }
            
            if (!is_array($callback)) continue;
            
            if (!($callback[0] instanceof \Nette\Database\Diagnostics\ConnectionPanel)) continue;
            
            $panel = $callback[0];
            if (count($panel->queries) < self::$maxQueries) continue;
            
            unset($this->connection->onQuery[$i]);
        }
        
        if (!isset($panel)) {
            $this->removeItself();
        }
    }
    
    
    private function removeItself() {
        foreach ($this->connection->onQuery as $i => $callback) {
            if ($callback instanceof \Nette\Callback) {
                $callback = $callback->getNative();
            }
            
            if (!is_array($callback)) continue;
            
            if (!($callback[0] instanceof static)) continue;
            
            unset($this->connection->onQuery[$i]);
        }
    }
    
}

