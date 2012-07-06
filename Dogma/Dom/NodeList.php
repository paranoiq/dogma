<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;


class NodeList extends \Dogma\Object implements \Countable, \Iterator {
    
    /** @var \DOMNodeList */
    private $nodeList;
    
    /** @var QueryEngine */
    private $engine;
    
    /** @var int */
    private $offset = 0;
    
    
    /**
     * @param \DOMNodeList
     * @param QueryEngine
     */
    public function __construct(\DOMNodeList $nodeList, QueryEngine $engine) {
        $this->nodeList = $nodeList;
        $this->engine = $engine;
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
     * @return Element|\DOMNode
     */
    private function wrap($node) {
        if ($node instanceof \DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }
    
}
