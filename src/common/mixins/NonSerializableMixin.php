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
     * @return mixed[]
     * @deprecated Serializing this class is forbidden
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
     * @deprecated Serializing this class is forbidden
     * @throws NonSerializableObjectException
     */
    final public function __wakeup(): void
    {
        throw new NonSerializableObjectException(static::class);
    }

}
