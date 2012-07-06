<?php

namespace Dogma\Dom;


class Document extends \DomDocument {
    
    /** @var QueryEngine */
    private $engine;
    
    
    /**
     * @param string $document XML or HTML content or file path prefixed with '@'
     * @param string
     * @param string
     */
    public function __construct($document = NULL, $version = '1.0', $encoding = 'utf-8') {
        parent::__construct($version, $encoding);
        
        if (!$document) return;
        
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
     * @param QueryEngine
     */
    public function setQueryEngine(QueryEngine $engine) {
        $this->engine = $engine;
    }
    
    
    /**
     * @return QueryEngine
     */
    public function getQueryEngine() {
        return $this->engine;
    }


    /**
     * @param string
     * @param int
     * @return bool
     */
    public function loadXml($source, $options = 0) {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadXML($source, $options)) {
            $error = libxml_get_last_error();
            throw new DomException("Cannot load HTML document: " . trim($error->message) . " on line #" . $error->line, $error->code);
        }
    }
    
    
    /**
     * @param string
     * @return bool
     */
    public function loadHtml($source) {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTML($source)) {
            $error = libxml_get_last_error();
            throw new DomException("Cannot load HTML document: " . trim($error->message) . " on line #" . $error->line, $error->code);
        }
    }

    
    /**
     * @param string
     * @return bool
     */
    public function loadHtmlFile($fileName) {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTMLFile($fileName)) {
            $error = libxml_get_last_error();
            throw new DomException("Cannot load HTML document: " . trim($error->message) . " on line #" . $error->line, $error->code);
        }
    }
    
    
    /**
     * @param string
     * @return Element|\DOMNode|null
     */
    public function getElementById($id) {
        $el = parent::getElementById($id);
        
        return $el ? $this->wrap($el) : NULL;
    }
    
    
    /**
     * @param string
     * @return NodeList
     */
    public function find($xpath) {
        return $this->engine->find($xpath);
    }

    
    /**
     * @param string
     * @return Element|\DOMNode
     */
    public function findOne($xpath) {
        return $this->engine->findOne($xpath);
    }
    
    
    /**
     * @param string
     * @return string|int|float
     */
    public function evaluate($xpath) {
        return $this->engine->evaluate($xpath);
    }


    /**
     * @param string|string[]
     * @return string|string[]
     */
    public function extract($target) {
        return $this->engine->extract($target);
    }
    
    
    public function dump() {
        Dumper::dump($this);
    }


    /**
     * @param \DOMNode
     * @return Element|\DOMNode
     */
    private function wrap($node) {
        if ($node instanceof \DOMElement) {
            return new Element($node, $this->engine);
        } else {
            return $node;
        }
    }
    
}
