<?php

namespace Dogma\Xml;


class DomDocument extends \DomDocument {
    
    /** @var XpathProcessor */
    private $xpathProcessor;
    
    
    /**
     * @param XpathProcessor
     */
    public function setXpathProcessor(XpathProcessor $xpathProcessor) {
        $this->xpathProcessor = $xpathProcessor;
    }
    
    
    /**
     * @return XpathProcessor
     */
    public function getXpathProcessor() {
        return $this->xpathProcessor;
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
    public function loadHtmlFile($filename) {
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        if (!parent::loadHTMLFile($filename)) {
            $error = libxml_get_last_error();
            throw new DomException("Cannot load HTML document: " . trim($error->message) . " on line #" . $error->line, $error->code);
        }
    }
    
    
    /**
     * @param string
     * @return DomNodeList
     */
    public function find($xpath) {
        return $this->xpathProcessor->find($xpath);
    }

    
    /**
     * @param string
     * @return DomElement|\DOMNode
     */
    public function findOne($xpath) {
        return $this->xpathProcessor->findOne($xpath);
    }
    
    
    /**
     * @param string
     * @return string|int|float
     */
    public function evaluate($xpath) {
        return $this->xpathProcessor->evaluate($xpath);
    }


    /**
     * @param string|string[]
     * @return string|string[]
     */
    public function extract($target) {
        return $this->xpathProcessor->extract($target);
    }
}
