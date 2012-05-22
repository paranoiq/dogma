<?php

namespace Dogma;

/**
 * Date class.
 * 
 * (interfaces commented out due to a strange PHP bug)
 */
class Date extends DateTime {


    /**
     * @param string
     * @param \DateTimeZone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = NULL) {
        parent::__construct($time, $timezone);
    }

    
    /**
     * @return string
     */
    public function __toString() {
        return $this->format('Y-m-d');
    }
    
}
