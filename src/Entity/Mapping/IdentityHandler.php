<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Mapping;

use Dogma\Entity\Identity;
use Dogma\Entity\Map\IdentityMap;
use Dogma\Mapping\Mapper;
use Dogma\Type;

/**
 * Creates identities from raw data and vice versa
 */
class IdentityHandler implements \Dogma\Mapping\Type\Handler
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Entity\Map\IdentityMap */
    private $identityMap;

    public function __construct(IdentityMap $identityMap)
    {
        $this->identityMap = $identityMap;
    }

    public function acceptsType(Type $type): bool
    {
        return $type->isImplementing(Identity::class);
    }

    /**
     * @param \Dogma\Type $type
     * @return \Dogma\Type[]
     */
    public function getParameters(Type $type): array
    {
        return [self::SINGLE_PARAMETER => Type::int()];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param int $value
     * @param \Dogma\Mapping\Mapper $mapper
     * @return \Dogma\Entity\Identity
     */
    public function createInstance(Type $type, $value, Mapper $mapper): Identity
    {
        return call_user_func([$type->getName(), 'get'], $this->identityMap, $value);
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint
     * @param \Dogma\Type $type
     * @param \Dogma\Entity\Identity $instance
     * @param \Dogma\Mapping\Mapper $mapper
     * @return int
     */
    public function exportInstance(Type $type, $instance, Mapper $mapper): int
    {
        return $instance->getId();
    }

}
