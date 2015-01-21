<?php

namespace Dogma;

trait NonIterableMixin
{

    /**
     * To avoid iterating through an object by accident
     * @throws \Dogma\NonIterableObjectException
     */
    public function getIterator()
    {
        throw new \Dogma\NonIterableObjectException(get_class($this));
    }

}
