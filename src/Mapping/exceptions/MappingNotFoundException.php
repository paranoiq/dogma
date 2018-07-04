<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping;

use Dogma\Exception;
use Dogma\Type;
use function sprintf;

class MappingNotFoundException extends Exception implements MappingException
{

    /** @var \Dogma\Type */
    private $type;

    public function __construct(Type $size, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Mapping for type %s was not found.', $size->getId()), $previous);
        $this->type = $size;
    }

    public function getType(): Type
    {
        return $this->type;
    }

}
