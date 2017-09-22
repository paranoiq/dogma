<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Mapping;

use Dogma\Entity\Entity;
use Dogma\Entity\Identity;
use Dogma\Entity\Map\EntityMap;
use Dogma\Entity\Proxy\EntityProxyFactory;
use Dogma\Mapping\Mapper;
use Dogma\Reflection\MethodTypeParser;
use Dogma\Type;

/**
 * Creates an entity from raw data and vice versa
 */
class EntityHandler extends \Dogma\Mapping\Type\ConstructorHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\Mapping\Type\ExportableHandlerMixin;

    /** @var \Dogma\Entity\Map\EntityMap */
    private $entityMap;

    /** @var \Dogma\Entity\Proxy\EntityProxyFactory */
    private $proxyFactory;

    public function __construct(MethodTypeParser $parser, EntityMap $entityMap, EntityProxyFactory $proxyFactory)
    {
        parent::__construct($parser);

        $this->entityMap = $entityMap;
        $this->proxyFactory = $proxyFactory;
    }

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(Entity::class);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param mixed[] $data
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Entity\Entity
     */
    public function createInstance(Type $type, $data, Mapper $mapper): Entity
    {
        if ($data instanceof Identity) {
            $entity = $this->entityMap->find($data, $type->getName());
            if ($entity !== null) {
                return $entity;
            } else {
                return $this->proxyFactory->createProxy($data, $type);
            }
        } else {
            /** @var \Dogma\Entity\Entity $entity */
            $entity = parent::createInstance($type, $data, $mapper);
            $this->entityMap->add($entity);

            return $entity;
        }
    }

}
