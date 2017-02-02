<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\MetaData;

use Dogma\Check;
use Dogma\Mapping\Type\Handler;
use Dogma\Type;

class TypeMetaDataContainer
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Mapping\Type\Handler[] */
    private $handlers;

    /** @var \Dogma\Mapping\MetaData\TypeMetaData[] (string $typeId => $typeMetaData) */
    private $types;

    /**
     * * @param \Dogma\Mapping\Type\Handler[] $handlers
     */
    public function __construct(array $handlers)
    {
        Check::itemsOfType($handlers, Handler::class);

        $this->handlers = $handlers;
    }
    
    public function getType(Type $type): TypeMetaData
    {
        $typeId = $type->getId();
        if (!isset($this->types[$typeId])) {
            $this->addType($type);
        }

        return $this->types[$typeId];
    }
    
    private function addType(Type $type)
    {
        $added = false;
        foreach ($this->handlers as $handler) {
            if ($handler->acceptsType($type)) {
                $params = $handler->getParameters($type);
                if ($params === null) {
                    $params = [Handler::SINGLE_PARAMETER => Type::get(Type::MIXED)];
                }
                $this->types[$type->getId()] = new TypeMetaData($type, $params, $handler);
                $added = true;
            }
        }
        if (!$added) {
            throw new \Dogma\Mapping\Type\NoHandlerForTypeException($type);
        }
    }

}
