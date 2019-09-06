<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use const PHP_VERSION;

trait NonSerializableMixin
{

    /**
     * To avoid serializing a non serializable object
     * @return mixed[]
     * @deprecated
     * @throws NonSerializableObjectException
     */
    final public function __sleep(): array
    {
        if (PHP_VERSION !== '') {
            throw new NonSerializableObjectException(static::class);
        }

        return [];
    }

    /**
     * To avoid serializing a non serializable object
     * @deprecated
     * @throws NonSerializableObjectException
     */
    final public function __wakeup(): void
    {
        throw new NonSerializableObjectException(static::class);
    }

}
