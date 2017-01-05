<?php

namespace Dogma;

class InvalidTypeDefinitionException extends \Dogma\Exception
{

    public function __construct(string $definition, \Throwable $previous = null)
    {
        $example = '\\Tuple<int(64,unsigned),float,\DateTime?>';
        parent::__construct(sprintf(
            'Type definition \'%s\' is invalid. Example of valid complex type definition: \'%s\'.',
            $definition,
            $example
        ), $previous);
    }

}
