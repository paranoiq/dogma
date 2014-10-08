<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Nette\ObjectMixin;
use Nette\MemberAccessException;


abstract class Object {

    /**
     * Call to undefined method.
     * @param  string $name method name
     * @param  array
     * @return mixed
     * @throws MemberAccessException
     */
    public function __call($name, $args) {
        return ObjectMixin::call($this, $name, $args);
    }


    /**
     * Call to undefined static method.
     * @param  string $name method name (in lower case!)
     * @param  array
     * @return mixed
     * @throws MemberAccessException
     */
    public static function __callStatic($name, $args) {
        return ObjectMixin::callStatic(get_called_class(), $name, $args);
    }


    /**
     * Returns property value. Do not call directly.
     * @param  string
     * @return mixed
     * @throws MemberAccessException if the property is not defined.
     */
    public function &__get($name) {
        return ObjectMixin::get($this, $name);
    }


    /**
     * Sets value of a property. Do not call directly.
     * @param  string
     * @param  mixed
     * @return void
     * @throws MemberAccessException if the property is not defined or is read-only
     */
    public function __set($name, $value) {
        ObjectMixin::set($this, $name, $value);
    }


    /**
     * Is property defined?
     * @param  string
     * @return bool
     */
    public function __isset($name) {
        return ObjectMixin::has($this, $name);
    }


    /**
     * Access to undeclared property.
     * @param  string
     * @return void
     * @throws MemberAccessException
     */
    public function __unset($name) {
        ObjectMixin::remove($this, $name);
    }

}
