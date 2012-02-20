<?php

namespace Dogma;


/**
 * DateTime
 */
class DateTime extends \DateTime {
    
    public $onChange = array();


    /**
     * @param string
     * @param \DateTimeZone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = NULL) {
        if ($time instanceof \DateTime)
            /** @noinspection PhpUndefinedMethodInspection */
            $time = $time->format("Y-m-d H:i:s");
        
        if ($timezone) {
            parent::__construct($time, $timezone);
        } else {
            parent::__construct($time);
        }
    }


    /**
     * @static
     * @param string
     * @param string
     * @param \DateTimeZone
     * @return self
     */
    public static function createFromFormat($format, $time, /*\DateTimeZone*/ $timezone = NULL) {
        if ($timezone) {
            $date = new static(parent::createFromFormat($format, $time, $timezone), $timezone);
        } else {
            $date = new static(parent::createFromFormat($format, $time));
        }
        
        return $date;
    }


    /**
     * @param \DateInterval
     * @return self
     */
    public function add(/*\DateInterval*/ $interval) {
        return parent::add($interval);
    }


    /**
     * @param \DateInterval
     * @return self
     */
    public function sub(/*\DateInterval*/ $interval) {
        return parent::sub($interval);
    }


    /**
     * @param string
     * @return self
     */
    public function modify($modify) {
        return parent::modify($modify);
    }


    /**
     * @param int
     * @param int
     * @param int
     * @return self
     */
    public function setDate($year, $month, $day) {
        return parent::setDate($year, $month, $day);
    }


    /**
     * @param int
     * @param int
     * @param int
     * @return self
     */
    public function setISODate($year, $week, $day = 1) {
        return parent::setISODate($year, $week, $day);
    }


    /**
     * @param int
     * @param int
     * @param int
     * @return self
     */
    public function setTime($hour, $minute, $second = 0) {
        return parent::setTime($hour, $minute, $second);
    }


    /**
     * @param int
     * @return self
     */
    public function setTimestamp($timestamp) {
        return parent::setTimestamp($timestamp);
    }


    /**
     * @param DateTimeZone
     * @return self
     */
    public function setTimezone(/*\DateTimeZone*/ $timezone) {
        return parent::setTimezone($timezone);
    }
    
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->format('Y-m-d H:i:s');
    }
    
    
    /**
     * Call to undefined method.
     * @param  string  method name
     * @param  array   arguments
     * @return mixed
     * @throws MemberAccessException
     */
    public function __call($name, $args) {
        return \Nette\ObjectMixin::call($this, $name, $args);
    }
    
}
