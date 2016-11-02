<?php

namespace Dogma;

trait NonSerializableMixin
{

    /**
     * To avoid serializing a non serializable object
     * @deprecated
     * @throws \Dogma\NonSerializableObjectException
     */
    final public function __sleep()
    {
        throw new \Dogma\NonSerializableObjectException(get_class($this));
    }

    /**
     * To avoid serializing a non serializable object
     * @deprecated
     * @throws \Dogma\NonSerializableObjectException
     */
    final public function __wakeup()
    {
        throw new \Dogma\NonSerializableObjectException(get_class($this));
    }

}
