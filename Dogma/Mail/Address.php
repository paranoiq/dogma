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
class Address extends \Dogma\Object
{

    /** @var string */
    private $name;

    /** @var string */
    private $address;

    //** @var bool */
    //private $group;


    /**
     * @param string
     * @param string
     */
    public function __construct($address, $name = null)
    {
        $this->address = strtolower($address);
        $this->name = $name;
    }


    /** @return string */
    public function getAddress()
    {
        return $this->address;
    }


    /** @return string */
    public function getName()
    {
        return $this->name;
    }


    public function __toString()
    {
        return $this->name ? sprintf('"%s" <%s>', $this->name, $this->address) : $this->address;
    }

}
