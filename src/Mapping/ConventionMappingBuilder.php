<?php declare(strict_types = 1);
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
use Dogma\Mapping\Naming\ShortFieldNamingStrategy;
use Dogma\Mapping\Type\TypeHandler;
use Dogma\StrictBehaviorMixin;
use Dogma\Type;
use function rtrim;

class ConventionMappingBuilder implements MappingBuilder
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Mapping\MetaData\TypeMetaDataContainer */
    private $typeMetaData;

    /** @var \Dogma\Mapping\Naming\NamingStrategy|null */
    private $fieldNamingStrategy;

    /** @var string */
    private $fieldSeparator;

    public function __construct(
        TypeMetaDataContainer $typeMetaDataContainer,
        ?NamingStrategy $fieldNamingStrategy = null,
        string $fieldSeparator = '.'
    )
    {
        $this->typeMetaData = $typeMetaDataContainer;
        $this->fieldNamingStrategy = $fieldNamingStrategy ?: new ShortFieldNamingStrategy();
        $this->fieldSeparator = $fieldSeparator;
    }

    public function buildMapping(Type $type): Mapping
    {
        $steps = [];
        $this->buildStep($type, '', TypeHandler::SINGLE_PARAMETER, $steps);

        return new Mapping($type, $steps);
    }

    /**
     * @param \Dogma\Type $type
     * @param string $path
     * @param string $destinationKey
     * @param \Dogma\Mapping\MappingStep[] $steps
     */
    private function buildStep(Type $type, string $path, string $destinationKey, array &$steps): void
    {
        $typeMetaData = $this->typeMetaData->getType($type);

        $fields = $typeMetaData->getFields();
        $fieldPath = rtrim($path . $this->fieldSeparator . $type->getName());
        $handlerKeys = [];
        foreach ($fields as $fieldHandlerKey => $fieldType) {
            $fieldDestinationKey = $destinationKey
                ? ($fieldHandlerKey ? $destinationKey . $this->fieldSeparator . $fieldHandlerKey : $destinationKey)
                : $fieldHandlerKey;
            if ($fieldType->is(Type::MIXED)) {
                $fieldSourceKey = $this->fieldNamingStrategy->translateName($fieldDestinationKey, $fieldPath, $this->fieldSeparator);
            } else {
                $fieldSourceKey = $fieldDestinationKey;
                $this->buildStep($fieldType, $fieldPath, $fieldDestinationKey, $steps);
            }
            $handlerKeys[$fieldSourceKey] = $fieldHandlerKey;
        }
        $step = new MappingStep($type, $typeMetaData->getHandler(), $handlerKeys, $destinationKey);

        $steps[] = $step;
    }

}
