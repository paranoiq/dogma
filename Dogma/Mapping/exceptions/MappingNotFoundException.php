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

class MappingNotFoundException extends \Dogma\Exception implements \Dogma\Mapping\Exception
{

    /** @var \Dogma\Type */
    private $type;

    public function __construct(Type $size, \Throwable $previous = null)
    {
        parent::__construct(sprintf('Mapping for type %s was not found.', $size->getId()), $previous);
        $this->type = $size;
    }

    /**
     * @return \Dogma\Type
     */
    public function getType()
    {
        return $this->type;
    }

}
