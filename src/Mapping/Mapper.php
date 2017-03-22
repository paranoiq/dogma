<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\Type;

class Mapper
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Mapping\MappingContainer */
    private $mappings;

    public function __construct(MappingContainer $mappings)
    {
        $this->mappings = $mappings;
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed[] $data
     * @return mixed
     */
    public function map(Type $type, array $data)
    {
        return $this->mappings->getMapping($type)->mapForward($data, $this);
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed $data
     * @return mixed[]
     */
    public function reverseMap(Type $type, $data): array
    {
        return $this->mappings->getMapping($type)->mapBack($data, $this);
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed[]|\Traversable $data
     * @return \Traversable
     */
    public function mapMany(Type $type, $data): \Traversable
    {
        $iterator = new MappingIterator($data, $type->getItemType(), $this);

        return $type->getInstance($iterator);
    }

    /**
     * @param \Dogma\Type $type
     * @param mixed[]|\Traversable $data
     * @return \Dogma\Mapping\MappingIterator
     */
    public function reverseMapMany(Type $type, $data): MappingIterator
    {
        return new MappingIterator($data, $type->getItemType(), $this, true);
    }

}
