<?php

namespace Dogma\Mapping;

use Dogma\Type;

class MappingNotFoundException extends \Dogma\Exception implements \Dogma\Mapping\Exception
{

    /** @var \Dogma\Type */
    private $type;
    
    public function __construct(Type $type, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Mapping for type %s was not found.', $type->getId()), $previous);
        $this->type = $type;
    }

    /**
     * @return \Dogma\Type
     */
    public function getType()
    {
        return $this->type;
    }

}
