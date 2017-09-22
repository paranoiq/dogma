<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity;

class RepositoryNotFoundException extends \Dogma\Exception implements \Dogma\Entity\Exception
{

    public function __construct(string $entityClass, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf('Repository for entities of class \'%s\' is not registered.', $entityClass), $previous);
    }

}
