<?php

namespace Dogma\Database\Reflection;


class ConventionalReflection extends \Nette\Database\Reflection\ConventionalReflection {
    
    
    /** @var array list of primatry columns which do not follow convention */
    private $exceptions = array();
    
    
    /**
     * Set 
     * @param array (table => primaryColumn)
     */
    public function setExceptions($exceptions = array()) {
        $this->exceptions = array_merge($this->exceptions, $exceptions);
    }
    
    
    public function getPrimary($table) {
        if (isset($this->exceptions[$table]))
            return $this->exceptions[$table];
        
        return parent::getPrimary($table);
    }
    
    
    public function getReferencedColumn($name, $table) {
        if (isset($this->exceptions[$name]))
            return $this->exceptions[$name];
        
        return parent::getReferencedColumn($name, $table);
    }
    
}
