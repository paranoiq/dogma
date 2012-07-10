<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mail;


/**
 * Mail address.
 * @property-read $address
 * @property-read $name
 */
class Address extends \Dogma\Object {
    
    /** @var string */
    private $address;
    
    /** @var string */
    private $name;
    
    /** @var bool */
    private $group;
    
    
    /**
     * @param string
     * @param string
     */
    public function __construct($address, $name = NULL, $group = FALSE) {
        $this->address = $address;
        $this->name = ($name === $address) ? NULL : $name;
        $this->group = (bool) $group;
    }
    
    
    /** @return string */
    public function getAddress() {
        return $this->address;
    }


    /** @return string */
    public function getName() {
        return $this->name;
    }


    /** @return bool */
    public function isGroup() {
        return $this->group;
    }
    
    
    public function __toString() {
        /// group?
        return $this->name ? "\"$this->name\" <$this->address>" : $this->address;
    }
    
    
    /**
     * Parse addresses from mail header (from, to, cc, reply-to, return-path, delivered-to, sender…)
     * @param string
     */
    public static function parseHeader($header) {
        $data = mailparse_rfc822_parse_addresses($header);
        
        $arr = array();
        foreach ($data as $item) {
            $arr[] = new self($item['address'], $item['display'], $item['is_group']);
        }
        
        return $arr;
    }
    
}
