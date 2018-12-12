<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\StrictBehaviorMixin;
use Dogma\Type;

class Mapper
{
    use StrictBehaviorMixin;

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
     * @param iterable|mixed[] $data
     * @return \Traversable|mixed[]
     */
    public function mapMany(Type $type, iterable $data): \Traversable
    {
        $iterator = new MappingIterator($data, $type->getItemType(), $this);

        /** @var \Traversable $result */
        $result = $type->getInstance($iterator);

        return $result;
    }

    /**
     * @param \Dogma\Type $type
     * @param iterable|mixed[] $data
     * @return \Dogma\Mapping\MappingIterator
     */
    public function reverseMapMany(Type $type, iterable $data): MappingIterator
    {
        return new MappingIterator($data, $type->getItemType(), $this, true);
    }

}
