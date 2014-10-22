<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Nette\Utils\ObjectMixin;
use Nette\MemberAccessException;


abstract class Object {

    /**
     * Call to undefined method.
     * @param string
     * @param array
     * @return mixed
     */
    public function __call($name, $args) {
        return ObjectMixin::call($this, $name, $args);
    }


    /**
     * Call to undefined static method.
     * @param string
     * @param array
     * @return mixed
     */
    public static function __callStatic($name, $args) {
        return ObjectMixin::callStatic(get_called_class(), $name, $args);
    }


    /**
     * Returns property value. Do not call directly.
     * @param string
     * @return mixed
     */
    public function &__get($name) {
        return ObjectMixin::get($this, $name);
    }


    /**
     * Sets value of a property. Do not call directly.
     * @param string
     * @param mixed
     * @return void
     */
    public function __set($name, $value) {
        ObjectMixin::set($this, $name, $value);
    }


    /**
     * Is property defined?
     * @param string
     * @return boolean
     */
    public function __isset($name) {
        return ObjectMixin::has($this, $name);
    }


    /**
     * Access to undeclared property.
     * @param string
     * @return void
     */
    public function __unset($name) {
        ObjectMixin::remove($this, $name);
    }

}
