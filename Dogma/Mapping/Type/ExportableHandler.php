<?php

namespace Dogma\Mapping\Type;

use Dogma\Type;

/**
 * Extracts raw data from instance via \Dogma\Exportable interface
 */
class ExportableHandler extends \Dogma\Mapping\Type\ConstructorHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\Mapping\Type\ExportableHandlerMixin;
    
    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(Exportable::class);
    }

}
