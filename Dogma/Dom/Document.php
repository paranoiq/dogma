<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Dom;


class Document extends \DomDocument
{

    /** @var \Dogma\Dom\QueryEngine */
    private $engine;

    /**
     * @param string $document XML or HTML content or file path prefixed with '@'
     * @param string
     * @param string
     */
    public function __construct($document = null, $version = '1.0', $encoding = 'utf-8')
    {
        parent::__construct($version, $encoding);

        if (!$document) {
            return;
        }

        if (substr($document, 0, 1) === '@') {

            ///

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

    /**
     * @param \Dogma\Dom\QueryEngine
     */
    public function setQueryEngine(QueryEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @return \Dogma\Dom\QueryEngine
     */
    public function getQueryEngine()
    {
        return $this->engine;
    }

    /**
     * @param string
     * @param integer
     * @return boolean
     */
    public function loadXml($source, $options = null)
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadXML($source, $options)) {
            $error = libxml_get_last_error();
            throw new DomException('Cannot load HTML document: ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        }
    }

    /**
     * @param string
     * @param integer
     * @return boolean
     */
    public function loadHtml($source, $options = null)
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTML($source, $options)) {
            $error = libxml_get_last_error();
            throw new DomException('Cannot load HTML document: ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        }
    }

    /**
     * @param string
     * @param integer
     * @return boolean
     */
    public function loadHtmlFile($fileName, $options = null)
    {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTMLFile($fileName)) {
            $error = libxml_get_last_error();
            throw new DomException('Cannot load HTML document: ' . trim($error->message) . ' on line #' . $error->line, $error->code);
        }
    }

    /**
     * @param string
     * @return \Dogma\Dom\Element|\DOMNode|null
     */
    public function getElementById($id)
    {
        $el = parent::getElementById($id);

        return $el ? $this->wrap($el) : null;
    }

    /**
     * @param string
     * @return \Dogma\Dom\NodeList
     */
    public function find($xpath)
    {
        return $this->engine->find($xpath);
    }

    /**
     * @param string
     * @return \Dogma\Dom\Element|\DOMNode
     */
    public function findOne($xpath)
    {
        return $this->engine->findOne($xpath);
    }

    /**
     * @param string
     * @return string|integer|float
     */
    public function evaluate($xpath)
    {
        return $this->engine->evaluate($xpath);
    }

    /**
     * @param string|string[]
     * @return string|string[]
     */
    public function extract($target)
    {
        return $this->engine->extract($target);
    }

    public function dump()
    {
        Dumper::dump($this);
    }

    /**
     * @param \DOMNode
     * @return \Dogma\Dom\Element|\DOMNode
     */
    private function wrap($node)
    {
        if ($node instanceof \DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }

}
