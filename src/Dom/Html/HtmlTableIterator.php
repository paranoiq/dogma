<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom\Html;

use Dogma\Dom\Element;
use Dogma\StrictBehaviorMixin;
use function count;
use function sprintf;

class HtmlTableIterator implements \Iterator
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Dom\Element */
    private $table;

    /** @var string */
    private $headRowSelector;

    /** @var string */
    private $bodyRowSelector;

    /** @var string[] */
    private $head;

    /** @var \Dogma\Dom\NodeList */
    private $rows;

    /** @var int */
    private $position;

    public function __construct(
        Element $table,
        string $headRowSelector = ':headrow',
        string $bodyRowSelector = ':bodyrow'
    )
    {
        if ($table->nodeName !== 'table') {
            throw new \InvalidArgumentException(sprintf('Element must be a table. %s given!', $table->nodeName));
        }

        $this->table = $table;
        $this->headRowSelector = $headRowSelector;
        $this->bodyRowSelector = $bodyRowSelector;
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

    public function valid(): bool
    {
        return $this->position < count($this->rows);
    }

    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return string[]
     */
    public function current(): array
    {
        return $this->formatRow($this->rows->item($this->position));
    }

    private function processTable(): void
    {
        foreach ($this->table->find($this->headRowSelector . '/:cell') as $cell) {
            $this->head[] = $cell->textContent;
        }
        $this->rows = $this->table->find($this->bodyRowSelector);
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
