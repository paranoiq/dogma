<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\Mapping\MetaData\TypeMetaDataContainer;
use Dogma\Mapping\Naming\NamingStrategy;
use Dogma\Type;

class ConfigurationMappingBuilder extends \Dogma\Mapping\ConventionMappingBuilder implements \Dogma\Mapping\MappingBuilder
{
    use \Dogma\StrictBehaviorMixin;

    /** @var mixed[] */
    private $configuration;

    /** @var \Dogma\Mapping\MetaData\TypeMetaDataContainer */
    private $typeMetaData;

    /** @var \Dogma\Mapping\Naming\NamingStrategy|null */
    private $fieldNamingStrategy;

    /**
     * @param mixed[] $configuration
     * @param \Dogma\Mapping\MetaData\TypeMetaDataContainer $typeMetaDataContainer
     * @param \Dogma\Mapping\Naming\NamingStrategy|null $fieldNamingStrategy
     */
    public function __construct(
        array $configuration,
        TypeMetaDataContainer $typeMetaDataContainer,
        ?NamingStrategy $fieldNamingStrategy = null
    ) {
        parent::__construct($typeMetaDataContainer, $fieldNamingStrategy);

        $this->configuration = $configuration;
        $this->typeMetaData = $typeMetaDataContainer;
        $this->fieldNamingStrategy = $fieldNamingStrategy;
    }

    public function buildMapping(Type $type): Mapping
    {
        $configuration = $this->getConfiguration($type);
        if (!$configuration) {
            return parent::buildMapping($type);
        }

        $steps = [];

        ///

        return new Mapping($type, $steps);
    }

    /**
     * @param \Dogma\Type $type
     * @return mixed[]
     */
    private function getConfiguration(Type $type): array
    {
        ///

        return [];
    }

}
