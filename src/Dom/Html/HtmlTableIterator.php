<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom\Html;

use Dogma\Dom\Element;

class HtmlTableIterator implements \Iterator
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Dom\Element */
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

    public function rewind(): void
    {
        if (!$this->head) {
            $this->processTable();
        }
        $this->position = 0;
    }

    public function next(): void
    {
        $this->position++;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->position < count($this->rows);
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return string[]
     */
    public function current(): string
    {
        return $this->formatRow($this->rows->item($this->position));
    }

    private function processTable(): void
    {
        foreach ($this->table->find(':headrow/:cell') as $cell) {
            $this->head[] = $cell->textContent;
        }
        $this->rows = $this->table->find(':bodyrow');
    }

    /**
     * @param \Dogma\Dom\Element $row
     * @return string[]
     */
    private function formatRow(Element $row): array
    {
        $res = [];
        foreach ($row->find(':cell') as $i => $cell) {
            $res[$this->head[$i]] = $cell->textContent;
        }
        return $res;
    }

}
