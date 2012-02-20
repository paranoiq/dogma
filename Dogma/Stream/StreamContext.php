<?php

namespace Dogma\Stream;

use Dogma\StreamException;


class StreamContext extends \Nette\object {
    
    /**
     * @var resource stream context
     */
    protected $context;
    
    
    /**
     * @param array array(wrapper=>array(name=>value))
     * @param callback stream notification callback     
     */
    public function __construct($options = array(), $callback = NULL) {
        $params = array();
        if ($callback) $params['notification'] = $callback; ///
        
        $this->context = stream_context_create($options, $params);
    }
    
    
    /**
     * @return resource stream context
     */
    public function getContext() {
        return $this->context;
    }
    
    
    /**
     * Set stream context option
     * @param string
     * @param string
     * @param mixed
     * @return StreamContext
     */
    public function setOption($wrapper, $option, $value) {
        if (stream_context_set_option($this->context, $wrapper, $option, $value)) {
            return $this;
        } else {
            throw new StreamException("Cannot set option $option for wrapper $wrapper.");
        }
    }
    
    
    /**
     * Get stream context options
     * @return array
     */
    public function getOptions() {
        return stream_context_get_options($this->context) {
    }
    
    
    /**
     * Set stream context option
     * @param string
     * @return StreamContext
     */
    public function setCallback($callback) {
        if (stream_context_set_params($this->context, array('notification' => $callback))) {
            return $this;
        } else {
            throw new StreamException("Cannot set notification callback.");
        }
    }
    
}
