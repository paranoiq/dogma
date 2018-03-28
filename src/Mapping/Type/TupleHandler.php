<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Type;

use Dogma\Mapping\Mapper;
use Dogma\StrictBehaviorMixin;
use Dogma\Tuple;
use Dogma\Type;

class TupleHandler implements TypeHandler
{
    use StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->is(Tuple::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return \Dogma\Type[]
     */
    public function getParameters(Type $type): array
    {
        return $type->getItemType();
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param mixed[] $items
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Tuple
     */
    public function createInstance(Type $type, $items, Mapper $mapper): Tuple
    {
        return new Tuple(...$items);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param \Dogma\Tuple $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): array
    {
        return $instance->toArray();
    }

}
