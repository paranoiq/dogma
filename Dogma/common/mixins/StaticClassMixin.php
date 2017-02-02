<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

trait StaticClassMixin
{

    /**
     * @throws \Dogma\StaticClassException
     */
    final public function __construct()
    {
        throw new \Dogma\StaticClassException(get_called_class());
    }

    /**
     * Call to undefined static method
     * @deprecated
     * @throws \Dogma\UndefinedMethodException
     */
    public static function __callStatic(string $name, $args)
    {
        throw new \Dogma\UndefinedMethodException(get_called_class(), $name);
    }

}
