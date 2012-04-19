<?php

namespace Dogma\Xml;


class HtmlTableIterator extends \Dogma\Object implements \Iterator {
    
    /** @var DomDocument */
    private $table;
    
    /** @var string[] */
    private $head;
    
    /** @var DomNodeList */
    private $rows;
    
    /** @var int */
    private $position;
    
    
    public function __construct(DomElement $table) {
        if ($table->nodeName !== 'table')
            throw new \InvalidArgumentException("Element must be a table. $table->nodeName given!");
        
        $this->table = $table;
    }
    
    
    public function rewind() {
        if (!$this->head) $this->processTable();
        $this->position = 0;
    }

    
    public function next() {
        $this->position++;
    }
    
    
    /**
     * @return bool
     */
    public function valid() {
        return $this->position < count($this->rows);
    }

    
    /**
     * @return int
     */
    public function key() {
        return $this->position;
    }

    
    /**
     * @return string[]
     */
    public function current() {
        return $this->formatRow($this->rows->item($this->position));
    }
    
    
    private function processTable() {
        foreach ($this->table->find(":headrow/:cell") as $cell) {
            $this->head[] = $cell->textContent;
        }
        $this->rows = $this->table->find(":bodyrow");
    }
    
    
    /**
     * @param DomElement
     * @return string[]
     */
    private function formatRow(DomElement $row) {
        $res = array();
        foreach ($row->find(":cell") as $i => $cell) {
            $res[$this->head[$i]] = $cell->textContent;
        }
        return $res;
    }
    
}
