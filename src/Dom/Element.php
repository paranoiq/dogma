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
use function array_shift;
use function call_user_func;
use function func_get_args;

/**
 * @property-read string $nodeName
 */
class Element
{
    use StrictBehaviorMixin;

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

    /**
     * @param string $xpath
     * @return \Dogma\Dom\Element|\DOMNode|null
     */
    public function findOne(string $xpath)
    {
        return $this->engine->findOne($xpath, $this->element);
    }

    /**
     * @param string $xpath
     * @return string|int|float
     */
    public function evaluate(string $xpath)
    {
        return $this->engine->evaluate($xpath, $this->element);
    }

    /**
     * @param string|string[] $target
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

    /**
     * @param string $name
     * @return mixed
     */
    public function &__get(string $name)
    {
        return $this->element->$name;
    }

    /**
     * @param string $name
     * @param mixed $arg
     * @return mixed
     */
    public function __call(string $name, $arg)
    {
        $args = func_get_args();

        return call_user_func([$this->element, $name], array_shift($args));
    }

    public function dump(): void
    {
        Dumper::dump($this);
    }

}
