<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


/**
 * Date and time class
 */
class DateTime extends \DateTime implements SimpleValueObject {

    /**
     * @param string
     * @param \DateTimeZone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null) {
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
     * @return self
     */
    public function setDefaultTimezone() {
        return $this->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    }


    /**
     * @param string
     * @param string
     * @param \DateTimeZone
     * @return self
     */
    public static function createFromFormat($format, $time, /*\DateTimeZone*/ $timezone = null) {
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
     * @param  string $name method name
     * @param  array  $args arguments
     * @return mixed
     */
    public function __call($name, $args) {
        return \Nette\ObjectMixin::call($this, $name, $args);
    }

}
