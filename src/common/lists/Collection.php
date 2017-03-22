<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Collection extends \Dogma\ImmutableArray
{

    /** @var string */
    protected $accepted;

    /**
     * @param string $accepted
     * @param object[] $items
     */
    public function __construct(string $accepted, array $items = [])
    {
        Check::className($accepted);

        parent::__construct($items);

        foreach ($this as $object) {
            $this->checkAccepted($object);
        }
        $this->accepted = $accepted;
    }

    /**
     * Check if object is of accepted class.
     * @param object $object
     * @throws \Dogma\InvalidTypeException
     */
    private function checkAccepted($object): void
    {
        if (!$object instanceof $this->accepted) {
            throw new \Dogma\InvalidTypeException($this->accepted, get_class($object));
        }
    }

}
