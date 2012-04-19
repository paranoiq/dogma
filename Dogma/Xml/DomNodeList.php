<?php

namespace Dogma\Xml;


class DomNodeList extends \Dogma\Object implements \Countable, \Iterator {
    
    /** @var \DOMNodeList */
    private $nodeList;
    
    /** @var XpathProcessor */
    private $xpathProcessor;
    
    /** @var int */
    private $offset = 0;
    
    
    /**
     * @param \DOMNodeList
     * @param XpathProcessor
     */
    public function __construct(\DOMNodeList $nodeList, XpathProcessor $xpathProcessor) {
        $this->nodeList = $nodeList;
        $this->xpathProcessor = $xpathProcessor;
    }
    
    
    public function item($offset) {
        return $this->wrap($this->nodeList->item($offset));
    }
    
    
    public function count() {
        // PHP bug - cannot count items using $length
        $n = 0;
        while ($this->nodeList->item($n)) {
            $n++;
        }
        return $n;
    }
    
    
    public function current() {
        return $this->wrap($this->nodeList->item($this->offset));
    }
    
    
    public function key() {
        return $this->offset;
    }
    
    
    public function next() {
        $this->offset++;
    }
    
    
    public function rewind() {
        $this->offset = 0;
    }
    
    
    public function valid() {
        // PHP bug - cannot iterate through items
        return $this->nodeList->item($this->offset) !== NULL;
    }
    
    
    /**
     * @param \DOMNode
     * @return DomElement|\DOMNode
     */
    private function wrap($node) {
        if ($node instanceof \DOMElement) {
            return new DomElement($node, $this->xpathProcessor);
        } else {
            return $node;
        }
    }
    
}
