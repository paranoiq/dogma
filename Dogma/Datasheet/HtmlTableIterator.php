<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Datasheet;

use Dogma\Dom\Element;


class HtmlTableIterator extends \Dogma\Object implements \Iterator
{

    /** @var Element */
    private $table;

    /** @var string[] */
    private $head;

    /** @var \Dogma\Dom\NodeList */
    private $rows;

    /** @var int */
    private $position;

    public function __construct(Element $table)
    {
        if ($table->nodeName !== 'table') {
            throw new \InvalidArgumentException(sprintf('Element must be a table. %s given!', $table->nodeName));
        }

        $this->table = $table;
    }

    public function rewind()
    {
        if (!$this->head) {
            $this->processTable();
        }
        $this->position = 0;
    }

    public function next()
    {
        $this->position++;
    }

    /**
     * @return boolean
     */
    public function valid()
    {
        return $this->position < count($this->rows);
    }

    /**
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return string[]
     */
    public function current()
    {
        return $this->formatRow($this->rows->item($this->position));
    }

    private function processTable()
    {
        foreach ($this->table->find(':headrow/:cell') as $cell) {
            $this->head[] = $cell->textContent;
        }
        $this->rows = $this->table->find(':bodyrow');
    }

    /**
     * @param \Dogma\Dom\Element
     * @return string[]
     */
    private function formatRow(Element $row)
    {
        $res = [];
        foreach ($row->find(':cell') as $i => $cell) {
            $res[$this->head[$i]] = $cell->textContent;
        }
        return $res;
    }

}
