<?php

namespace Dogma;


/**
 * DateTime
 */
class DateTime extends \DateTime {
    
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
