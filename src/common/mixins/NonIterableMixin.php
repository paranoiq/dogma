<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

/**
 * @deprecated PHPStan reports invalid usage of iteration. Use it instead
 */
trait NonIterableMixin
{

    /**
     * To avoid iterating through an object by accident
     * @deprecated
     * @throws \Dogma\NonIterableObjectException
     */
    public function getIterator(): void
    {
        throw new NonIterableObjectException(static::class);
    }

}
