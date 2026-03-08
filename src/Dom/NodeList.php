<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Countable;
use Dogma\StrictBehaviorMixin;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Iterator;
use ReturnTypeWillChange;

/**
 * @implements Iterator<int, Element|DOMNode>
 */
class NodeList implements Countable, Iterator
{
    use StrictBehaviorMixin;

    /** @var DOMNodeList<DOMNode> */
    private DOMNodeList $nodeList;

    private QueryEngine $engine;

    private int $offset = 0;

    /**
     * @param DOMNodeList<DOMNode> $nodeList
     */
    public function __construct(DOMNodeList $nodeList, QueryEngine $engine)
    {
        $this->nodeList = $nodeList;
        $this->engine = $engine;
    }

    public function item(int $offset): Element|DOMNode
    {
        /** @var DOMNode $item */
        $item = $this->nodeList->item($offset);

        return $this->wrap($item);
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

    #[ReturnTypeWillChange]
    public function current(): Element|DOMNode
    {
        /** @var DOMNode $item */
        $item = $this->nodeList->item($this->offset);

        return $this->wrap($item);
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

    private function wrap(DOMNode $node): Element|DOMNode
    {
        if ($node instanceof DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }

}
