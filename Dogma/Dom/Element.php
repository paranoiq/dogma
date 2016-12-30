<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

/**
 * @property-read string $nodeName
 */
class Element
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Dom\QueryEngine */
    private $engine;

    /** @var \DOMElement */
    private $element;

    public function __construct(\DOMElement $element, QueryEngine $engine)
    {
        $this->element = $element;
        $this->engine = $engine;
    }

    public function find(string $xpath): NodeList
    {
        return $this->engine->find($xpath, $this->element);
    }

    public function findOne(string $xpath): \DOMNode
    {
        return $this->engine->findOne($xpath, $this->element);
    }

    /**
     * @param string
     * @return string|int|float
     */
    public function evaluate(string $xpath)
    {
        return $this->engine->evaluate($xpath, $this->element);
    }

    /**
     * @param string|string[]
     * @return string|string[]
     */
    public function extract($target)
    {
        return $this->engine->extract($target, $this->element);
    }

    public function getElement(): \DOMElement
    {
        return $this->element;
    }

    public function remove(): bool
    {
        $this->element->parentNode->removeChild($this->element);

        return true;
    }

    public function &__get(string $name)
    {
        $val = $this->element->$name;

        return $val;
    }

    public function __call(string $name, $arg)
    {
        $args = func_get_args();

        return call_user_func(array($this->element, $name), array_shift($args));
    }

    public function dump()
    {
        Dumper::dump($this);
    }

}
