<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use BadMethodCallException;
use ReturnTypeWillChange;
use function array_key_exists;

trait ImmutableArrayAccessMixin
{

    public function offsetExists(mixed $key): bool
    {
        return array_key_exists($key, $this->toArray());
    }

    #[ReturnTypeWillChange]
    public function offsetGet(mixed $key): mixed
    {
        return $this->items[$key];
    }

    /**
     * @throws BadMethodCallException
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        throw new BadMethodCallException('Cannot modify an item of immutable list.');
    }

    /**
     * @throws BadMethodCallException
     */
    public function offsetUnset(mixed $key): void
    {
        throw new BadMethodCallException('Cannot unset an item of immutable list.');
    }

}
