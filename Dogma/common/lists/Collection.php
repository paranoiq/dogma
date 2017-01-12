<?php

namespace Dogma;

class Collection extends \Dogma\ImmutableArray
{

    /** @var string */
    protected $accepted;

    /**
     * @param string $accepted
     * @param object[] $items
     */
    public function __construct(string $accepted, $items = [])
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
     * @param object
     * @throws \Dogma\InvalidTypeException
     */
    private function checkAccepted($object)
    {
        if (!$object instanceof $this->accepted) {
            throw new \Dogma\InvalidTypeException($this->accepted, get_class($object));
        }
    }

}
