<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Reflection;

/**
 * Discovered reflection with optional exceptions.
 */
class DiscoveredReflection extends \Nette\Database\Reflection\DiscoveredReflection {
    
    
    /** @var array list of primatry columns which do not follow convention */
    private $exceptions = array();

    /**
     * Create autodiscovery structure.
     * @param  Nette\Caching\IStorage
     */
    public function __construct(\Nette\Caching\IStorage $storage = NULL, $exceptions = array()) {
        parent::__construct($storage);
        $this->setExceptions($exceptions);
    }
    
    
    /**
     * Set 
     * @param array (table => primaryColumn)
     */
    public function setExceptions($exceptions = array()) {
        $this->exceptions = array_merge($this->exceptions, $exceptions);
    }


    /**
     * @param string
     * @return string|NULL
     */
    public function getPrimary($table) {
        if (isset($this->exceptions[$table]))
            return $this->exceptions[$table];
        
        return parent::getPrimary($table);
    }


    /**
     * @param string
     * @param string
     * @return mixed
     */
    /*public function getReferencedColumn($name, $table) {
        if (isset($this->exceptions[$name]))
            return $this->exceptions[$name];
        
        return parent::getReferencedColumn($name, $table);
    }*/
    
}


