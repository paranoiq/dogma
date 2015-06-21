<?php

namespace Dogma\Mapping\Type;

use Dogma\Type;

class NoHandlerForTypeException extends \Dogma\Exception implements \Dogma\Mapping\Type\Exception
{

    /** @var \Dogma\Type */
    private $type;
    
    public function __construct(Type $type, \Throwable $previous = null)
    {
        parent::__construct(
            sprintf('No type handler for type %s is registered.', $type->getId()),
            $previous
        );
        $this->type = $type;
    }
    
    public function getType(): Type
    {
        return $this->type;
    }

}
