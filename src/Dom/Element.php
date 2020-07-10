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
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use DOMDocument;
use DOMElement;
use DOMNamedNodeMap;
use DOMNode;
use DOMNodeList;
use function array_shift;
use function call_user_func;
use function func_get_args;

/**
 * @property-read string $nodeName
 * @property-read string $nodeValue
 * @property-read int $nodeType
 * @property-read DOMNode|null $parentNode
 * @property-read DOMNodeList $childNodes
 * @property-read DOMElement|null $firstChild
 * @property-read DOMElement|null $lastChild
 * @property-read DOMElement|null $previousSibling
 * @property-read DOMElement|null $nextSibling
 * @property-read bool $schemaTypeInfo
 * @property-read string $tagName
 * @property-read DOMNamedNodeMap|null $attributes
 * @property-read DOMDocument|null $ownerDocument
 * @property-read string|null $namespaceUri
 * @property-read string|null $prefix
 * @property-read string $localName
 * @property-read string|null $baseUri
 * @property-read string $textContent
 */
class Element
{
    use StrictBehaviorMixin;

    /** @var QueryEngine */
    private $engine;

    /** @var DOMElement */
    private $element;

    public function __construct(DOMElement $element, QueryEngine $engine)
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
     * @return Element|DOMNode|null
     */
    public function findOne(string $xpath)
    {
        return $this->engine->findOne($xpath, $this->element);
    }

    /**
     * @param string $xpath
     * @return string|int|float|bool|Date|DateTime|null
     */
    public function evaluate(string $xpath)
    {
        return $this->engine->evaluate($xpath, $this->element);
    }

    /**
     * @param string|string[] $target
     * @return int|float|bool|string|Date|DateTime|mixed[]|null
     */
    public function extract($target)
    {
        return $this->engine->extract($target, $this->element);
    }

    public function getElement(): DOMElement
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
