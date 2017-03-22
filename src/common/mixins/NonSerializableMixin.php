<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

trait NonSerializableMixin
{

    /**
     * To avoid serializing a non serializable object
     * @deprecated
     * @throws \Dogma\NonSerializableObjectException
     */
    final public function __sleep(): void
    {
        throw new \Dogma\NonSerializableObjectException(get_class($this));
    }

    /**
     * To avoid serializing a non serializable object
     * @deprecated
     * @throws \Dogma\NonSerializableObjectException
     */
    final public function __wakeup(): void
    {
        throw new \Dogma\NonSerializableObjectException(get_class($this));
    }

}
