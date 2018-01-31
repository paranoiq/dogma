<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class CallbackIterator extends \IteratorIterator
{
    use \Dogma\StrictBehaviorMixin;

    /** @var callable */
    private $callback;

    public function __construct(iterable $iterable, callable $callback)
    {
        $this->callback = $callback;

        $iterable = IteratorHelper::iterableToIterator($iterable);

        parent::__construct($iterable);
    }

    /**
     * @return mixed
     */
    public function current()
    {
        $current = parent::current();

        return call_user_func($this->callback, $current);
    }

}
