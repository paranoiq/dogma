<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;

use Dogma\NotImplementedException;
use function libxml_clear_errors;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use function preg_match;
use function substr;
use function trim;

class Document extends \DOMDocument
{

    /** @var \Dogma\Dom\QueryEngine */
    private $engine;

    /**
     * XML or HTML content or file path prefixed with '@'
     * @param string|null $document
     * @param string $version
     * @param string $encoding
     */
    public function __construct(?string $document = null, string $version = '1.0', string $encoding = 'utf-8')
    {
        parent::__construct($version, $encoding);

        if (!$document) {
            return;
        }

        if (substr($document, 0, 1) === '@') {
            /// from file
            throw new NotImplementedException('File ');
        } else {
            if (preg_match('/<!DOCTYPE\\s+HTML/i', $document)) {
                $this->loadHtml($document);
            } elseif (preg_match('/\\s*<\\?xml/i', $document)) {
                $this->loadXml($document);
            } else {
                $this->loadHtml($document);
            }
        }

        $this->engine = new QueryEngine($this);
    }

    public function setQueryEngine(QueryEngine $engine): void
    {
        $this->engine = $engine;
    }

    public function getQueryEngine(): QueryEngine
    {
        return $this->engine;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $source
     * @param int|null $options
     */
    public function loadXml($source, $options = null): void
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadXML($source, $options)) {
            $error = libxml_get_last_error();
            throw new DomException('Cannot load HTML document: ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $source
     * @param int|null $options
     */
    public function loadHtml($source, $options = null): void
    {
        $previousState = libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTML($source, $options)) {
            $error = libxml_get_last_error();
            libxml_use_internal_errors($previousState);
            throw new DomException('Cannot load HTML document: ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        }
        libxml_use_internal_errors($previousState);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $fileName
     * @param int|null $options
     */
    public function loadHtmlFile($fileName, $options = null): void
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTMLFile($fileName)) {
            $error = libxml_get_last_error();
            throw new DomException('Cannot load HTML document: ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        }
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param string $id
     * @return \Dogma\Dom\Element|\DOMNode|null
     */
    public function getElementById($id)
    {
        $element = parent::getElementById($id);

        return $element ? $this->wrap($element) : null;
    }

    public function find(string $xpath): NodeList
    {
        return $this->engine->find($xpath);
    }

    /**
     * @param string $xpath
     * @return \Dogma\Dom\Element|\DOMNode
     */
    public function findOne(string $xpath)
    {
        return $this->engine->findOne($xpath);
    }

    /**
     * @param string $xpath
     * @return string|int|float
     */
    public function evaluate(string $xpath)
    {
        return $this->engine->evaluate($xpath);
    }

    /**
     * @param string|string[] $target
     * @return string|string[]
     */
    public function extract($target)
    {
        return $this->engine->extract($target);
    }

    public function dump(): void
    {
        Dumper::dump($this);
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

}
