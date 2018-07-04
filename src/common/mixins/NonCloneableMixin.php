<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function get_class;

trait NonCloneableMixin
{

    /**
     * To avoid cloning a non cloneable object
     * @deprecated
     * @throws \Dogma\NonCloneableObjectException
     */
    final public function __clone()
    {
        throw new NonCloneableObjectException(get_class($this));
    }

}
