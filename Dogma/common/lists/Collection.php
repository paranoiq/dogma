<?php

namespace Dogma;

class Collection extends \Dogma\ImmutableArray
{

    /** @var string */
    protected $accepted;

    /**
     * @param string $accepted
     * @param object[] $array
     */
    public function __construct($accepted, $array = [])
    {
        Check::className($accepted);

        parent::__construct($array);

        $this->accepted = $accepted;
        foreach ($this as $object) {
            $this->checkAccepted($object);
        }
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
