<?php

namespace Dogma;

/**
 * Date class. Allways keps the time at 00:00:00
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
        parent::setTime(0, 0, 0);
    }


    /**
     * @param \DateInterval
     * @return \Dogma\Date
     */
    public function add(/*\DateInterval*/ $interval) {
        $v = parent::add($interval);
        parent::setTime(0, 0, 0);
        return $v ? $this : FALSE;
    }


    /**
     * @param \DateInterval
     * @return \Dogma\Date
     */
    public function sub(/*\DateInterval*/ $interval) {
        $v = parent::sub($interval);
        parent::setTime(0, 0, 0);
        return $v ? $this : FALSE;
    }


    /**
     * @param string
     * @return \Dogma\Date
     */
    public function modify($modify) {
        $v = parent::modify($modify);
        parent::setTime(0, 0, 0);
        return $v ? $this : FALSE;
    }


    /**
     * @param int
     * @return \Dogma\Date
     */
    public function setTimestamp($timestamp) {
        $v = parent::setTimestamp($timestamp);
        parent::setTime(0, 0, 0);
        return $v ? $this : FALSE;
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->format('Y-m-d');
    }
    
}
