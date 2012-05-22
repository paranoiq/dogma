<?php

namespace Dogma\Database\Table;

use Dogma\Language\Inflector;


class ActiveRow extends \Nette\Database\Table\ActiveRow {
    
    
    /**
     * Do not call from inside of object! use set() instead!
     */
    public function __set($key, $value) {
        if (method_exists($this, "set$key")) 
            return call_user_func(array($this, "set$key"), $value);
        
        $this->set($key, $value);
    }
    
    
    /** @internal */
    public function set($key, $value) {
        $ukey = Inflector::underscore($key);
        parent::__set($ukey, $value);
    }
    
    
    /**
     * Do not call from inside of object! use get() instead!
     */
    public function &__get($key) {
        if (method_exists($this, "get$key")) {
            $value = call_user_func(array($this, "get$key"));
        } else {
            $value = $this->get($key);
        }
        
        return $value;
    }
    
    
    public function setNotifier($object, $key) {
        $that = $this;
        $object->onChange['Dogma'] = function () use ($that, $key, $object) {
            $that->set($key, $object);
        };
    }
    
    
    /** @internal */
    public function get($key) {
        $ukey = Inflector::underscore($key);
        return parent::__get($ukey);
    }
    
    
    public function __isset($key) {
        $ukey = Inflector::underscore($key);
        return parent::__isset($ukey, $value);
    }
    
    
    public function __unset($key) {
        $ukey = Inflector::underscore($key);
        parent::__unset($ukey, $value);
    }
    
}
