<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\SqlGenerator;

use Dogma\Mapping\MappingContainer;
use Dogma\Type;

class CreateTableGenerator
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Mapping\MappingContainer */
    private $mappingContainer;

    public function __construct(MappingContainer $mappingContainer)
    {
        $this->mappingContainer = $mappingContainer;
    }

    public function generate(Type $type): string
    {
        $mapping = $this->mappingContainer->getMapping($type);

        dump($mapping);

        return '';
    }

}
