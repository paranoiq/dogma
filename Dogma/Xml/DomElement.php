<?php

namespace Dogma\Xml;


class DomElement extends \Dogma\Object {
    
    
    private $xpathProcessor;
    
    private $element;
    
    
    public function __construct(\DOMElement $element, XpathProcessor $xpathProcessor) {
        $this->element = $element;
        $this->xpathProcessor = $xpathProcessor;
    }
    
    
    /**
     * @param string
     * @return \DOMNode
     */
    public function find($xpath) {
        return $this->xpathProcessor->find($xpath, $this->element);
    }


    /**
     * @param string
     * @return \DOMNode
     */
    public function findOne($xpath) {
        return $this->xpathProcessor->findOne($xpath, $this->element);
    }


    /**
     * @param string
     * @return string|int|float
     */
    public function evaluate($xpath) {
        return $this->xpathProcessor->evaluate($xpath, $this->element);
    }


    /**
     * @param string|string[]
     * @return string|string[]
     */
    public function extract($target) {
        return $this->xpathProcessor->extract($target, $this->element);
    }
    
    
    
    public function &__get($name) {
        $val = $this->element->$name;
        return $val;
    }
    
    
    public function __call($name, $arg) {
        $args = func_get_args();
        return call_user_func(array($this->element, $name), array_shift($args));
    }
    
}
