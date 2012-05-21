<?php

namespace Dogma\Xml;


class Dumper {
    
    public static function dump($node, $maxDepth = 12, $depth = 0, $only = FALSE) {
        if ($depth > $maxDepth) echo "…";
        if ($depth === 0) echo "<pre><code>";
        
        if ($node instanceof Element || $node instanceof \DOMElement) {
            if ($depth === 0) echo "<b>Element:</b>\n";
            if (!$only) echo str_repeat('    ', $depth); 
            echo "<b>&lt;</b><b style='color:red'>", $node->nodeName, "</b>";
            
            foreach ($node->attributes as $attribute) {
                echo " <span style='color: green'>", $attribute->name, "</span>=<span style='color:blue'>'", $attribute->value, "'</span>";
            }
            
            echo "<b>&gt;</b>";
            
            if ($node->childNodes->length > 1) {
                echo "\n";
            }
            foreach ($node->childNodes as $child) {
                self::dump($child, $maxDepth, $depth + 1, $node->childNodes->length === 1);
            }
            if ($node->childNodes->length > 1) {
                echo str_repeat('    ', $depth);
            }
            
            echo "<b>&lt;</b>/<b style='color: red'>", $node->nodeName, "</b><b>&gt;</b>";
            if (!$only) echo "\n";
            
        } elseif ($node instanceof \DOMDocument) {
            if ($depth === 0) echo "<b>Document:</b>\n";
            self::dump($node->documentElement, $maxDepth);
            
        } elseif ($node instanceof \DOMCdataSection) {
            if ($depth === 0) echo "<b>CdataSection:</b>\n";
            echo "<i style='color: purple'>", htmlspecialchars(trim($node->data)), "</i>";
            
        } elseif ($node instanceof \DOMComment) {
            if ($depth === 0) echo "<b>Comment:</b>\n";
            echo "<i style='color: gray'>&lt;!-- ", trim($node->data), " --&gt;</i>\n";
            
        } elseif ($node instanceof \DOMText) {
            if ($depth === 0) echo "<b>Text:</b>\n";
            $str = preg_replace("/[ \\t]+/", " ", trim($node->wholeText));
            echo "<i>", $str, "</i>";
            
        } elseif ($node instanceof NodeList) {
            echo "<b>NodeList (", count($node), ")</b>\n";
            foreach ($node as $item) {
                echo "<hr style='border: 1px silver solid; border-width: 1px 0px 0px 0px'>";
                echo "    ";
                self::dump($item, $maxDepth, $depth + 1, TRUE);
            }
            
        } else {
            echo "[something]";
            throw new \Exception('STOP');
            //dump($node);
        }

        if ($depth === 0) echo "<code></pre>";
    }
    
}
