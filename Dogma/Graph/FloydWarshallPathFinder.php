<?php

namespace Dogma\Graph;


/**
 * Floyd-Warshall algorythm for finding all shortest paths in oriented weighted graph.
 * All the hard work is done in constructor to enable serialisation and caching.
 * 
 * @see http://en.wikipedia.org/wiki/Floyd–Warshall_algorithm
 * @see https://github.com/pierre-fromager/PeopleFloydWarshall/blob/4731f8d1e6dd5e659f5945d03ddf8746a578a665/class/floyd-warshall.class.php
 */
class FloydWarshallPathFinder extends \Dogma\Object {
    
    /** @var array */
    private $weights;
    
    /** @var integer */
    private $nodeCount;
    
    /** @var array */
    private $nodeNames;
    
    /** @var array */
    private $distances = array(array());
    
    /** @var array */
    private $predecessors = array(array());
    
    
    /**
     * @param int[][] graph edge weights. may be sparse
     */
    function __construct($weights) {
        
        // array: assumption, that all nodes has an outgoing edge
        if (array_keys($weights) === range(0, count($weights))) {
            $this->weights = $weights;
            /// bug: wrong if last nodes has no outgoing edges
            $this->nodeCount = count($this->weights);
            
        // hashmap: replace keys with numeric indexes
        } else {
            $n = 0;
            $nodeNames = array();
            $normalized = array();
            foreach ($weights as $i => $nodes) {
                if (!isset($nodeNames[$i])) $nodeNames[$i] = $n++;
                foreach ($nodes as $j => $weight) {
                    if (!isset($nodeNames[$j])) $nodeNames[$j] = $n++;
                    $normalized[$nodeNames[$i]][$nodeNames[$j]] = $weight;
                }
            }
            $this->weights = $normalized;
            $this->nodeNames = $nodeNames;
            $this->nodeCount = count($nodeNames);
        }
        
        $this->calculatePaths();
    }
    
    
    /**
     * Implementation of Floyd-Warshall algorithm
     */
    private function calculatePaths() {
        // init
        for ($i = 0; $i < $this->nodeCount; $i++) {
            for ($j = 0; $j < $this->nodeCount; $j++) {
                if ($i === $j) {
                    $this->distances[$i][$j] = 0;
                } elseif (isset($this->weights[$i][$j]) && $this->weights[$i][$j] > 0) {
                    $this->distances[$i][$j] = $this->weights[$i][$j];
                } else {
                    $this->distances[$i][$j] = PHP_INT_MAX;
                }
                $this->predecessors[$i][$j] = $i;
            }
        }
        
        // run
        for ($k = 0; $k < $this->nodeCount; $k++) {
            for ($i = 0; $i < $this->nodeCount; $i++) {
                for ($j = 0; $j < $this->nodeCount; $j++) {
                    if ($this->distances[$i][$j] > ($this->distances[$i][$k] + $this->distances[$k][$j])) {
                        $this->distances[$i][$j] = $this->distances[$i][$k] + $this->distances[$k][$j];
                        $this->predecessors[$i][$j] = $this->predecessors[$k][$j];
                    }
                }
            }
        }
    }
    
    
    /**
     * Get total cost (distance) between point a and b
     * @param int|string
     * @param int|string
     * @return int
     */
    public function getDistance($i, $j) {
        if (!empty($this->nodeNames)) {
            $i = $this->nodeNames[$i];
            $j = $this->nodeNames[$j];
        }
        
        return $this->distances[$i][$j];
    }
    
    
    /**
     * Get nodes between a and b
     * @param int|string
     * @param int|string
     * @return int[]|string[]
     */
    public function getPath($i, $j) {
        if (!empty($this->nodeNames)) {
            $i = $this->nodeNames[$i];
            $j = $this->nodeNames[$j];
        }
        
        $path = array();
        $k = $j;
        do {
            $path[] = $k;
            $k = $this->predecessors[$i][$k];
        } while ($i != $k);
        
        return array_reverse($path);
    }
    
    
    /**
     * Print out the original Graph matrice
     * @return string html table
     */
    public function printGraphMatrix() {
        $rt = "<table>\n";
        if (!empty($this->nodeNames)) {
            $rt .= "<tr>";
            $rt .= "<td>&nbsp;</td>";
            for ($n = 0; $n < $this->nodeCount; $n++) {
                $rt .= "<td>" . $this->nodeNames[$n] . "</td>";
            }
        }
        $rt .= "</tr>";
        for ($i = 0; $i < $this->nodeCount; $i++) {
            $rt .= "<tr>";
            if (!empty($this->nodeNames)) {
                $rt .= "<td>" . $this->nodeNames[$i] . "</td>";
            }
            for ($j = 0; $j < $this->nodeCount; $j++) {
                $rt .= "<td>" . $this->weights[$i][$j] . "</td>";
            }
            $rt .= "</tr>";
        }
        $rt .= "</table>";
        return $rt;
    }
    
    
    /**
     * Print out distances matrice
     * @return string html table
     */
    public function printDistances() {
        $rt = "<table>\n";
        if (! empty($this->nodeNames) ) {
            $rt .= "<tr>";
            $rt .= "<td>&nbsp;</td>\n";
            for ($n = 0; $n < $this->nodeCount; $n++) {
                $rt .= "<td>" . $this->nodeNames[$n] . "</td>";
            }
        }
        $rt .= "</tr>";
        for ($i = 0; $i < $this->nodeCount; $i++) {
            $rt .= "<tr>";
            if (! empty($this->nodeNames) ) {
                $rt .= "<td>" . $this->nodeNames[$i] . "</td>\n";
            }
            for ($j = 0; $j < $this->nodeCount; $j++) {
                $rt .= "<td>" . $this->distances[$i][$j] . "</td>\n";
            }
            $rt .= "</tr>";
        }
        $rt .= "</table>\n";
        return $rt;
    }
    
    
    /**
     * Print out predecessors matrice
     * @return string html table
     */
    public function printPredecessors() {
        $rt = "<table>\n";
        if (!empty($this->nodeNames)) {
            $rt .= "<tr>";
            $rt .= "<td>&nbsp;</td>";
            for ($n = 0; $n < $this->nodeCount; $n++) {
                $rt .= "<td>" . $this->nodeNames[$n] . "</td>\n";
            }
        }
        $rt .= "</tr>";
        for ($i = 0; $i < $this->nodeCount; $i++) {
            $rt .= "<tr>";
            if (!empty($this->nodeNames)) {
                $rt .= "<td>" . $this->nodeNames[$i] . "[$i]</td>\n";
            }
            for ($j = 0; $j < $this->nodeCount; $j++) {
                $rt .= "<td>" . $this->predecessors[$i][$j] . "</td>\n";
            }
            $rt .= "</tr>\n";
        }
        $rt .= "</table>\n";
        return $rt;
    }
    
}
