<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

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