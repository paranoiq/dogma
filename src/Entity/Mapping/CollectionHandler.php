<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Mapping;

use Dogma\Collection;
use Dogma\Mapping\Mapper;
use Dogma\Type;

/**
 * Creates a collection containing specified items from raw data and vice versa
 */
class CollectionHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(Collection::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return mixed[]|null
     */
    public function getParameters(Type $type): ?array
    {
        return null;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param mixed[] $items
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Collection
     */
    public function createInstance(Type $type, $items, Mapper $mapper): Collection
    {
        ///

        return new Collection($type->getName());
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param mixed $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return mixed[]
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): array
    {
        ///

        return [];
    }

}
