<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Dogma\StrictBehaviorMixin;

class NodeList implements \Countable, \Iterator
{
    use StrictBehaviorMixin;

    /** @var \DOMNodeList */
    private $nodeList;

    /** @var \Dogma\Dom\QueryEngine */
    private $engine;

    /** @var int */
    private $offset = 0;

    public function __construct(\DOMNodeList $nodeList, QueryEngine $engine)
    {
        $this->nodeList = $nodeList;
        $this->engine = $engine;
    }

    /**
     * @param int $offset
     * @return \Dogma\Dom\Element|\DOMNode
     */
    public function item(int $offset)
    {
        return $this->wrap($this->nodeList->item($offset));
    }

    public function count(): int
    {
        // PHP bug - cannot count items using $length
        $n = 0;
        while ($this->nodeList->item($n)) {
            $n++;
        }
        return $n;
    }

    /**
     * @return \Dogma\Dom\Element|\DOMNode
     */
    public function current()
    {
        return $this->wrap($this->nodeList->item($this->offset));
    }

    public function key(): int
    {
        return $this->offset;
    }

    public function next(): void
    {
        $this->offset++;
    }

    public function rewind(): void
    {
        $this->offset = 0;
    }

    public function valid(): bool
    {
        // PHP bug - cannot iterate through items
        return $this->nodeList->item($this->offset) !== null;
    }

    /**
     * @param \DOMNode $node
     * @return \Dogma\Dom\Element|\DOMNode
     */
    private function wrap(\DOMNode $node)
    {
        if ($node instanceof \DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }

    public function dump(): void
    {
        Dumper::dump($this);
    }

}
