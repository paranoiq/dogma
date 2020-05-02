<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Call
{
    use StaticClassMixin;

    /**
     * Call function with each given value as param
     *
     * @param callable $function
     * @param iterable|mixed[] $values
     */
    public static function with(callable $function, iterable $values): void
    {
        foreach ($values as $value) {
            $function($value);
        }
    }

    /**
     * Call function with each set of given arguments
     *
     * @param callable $function
     * @param iterable|mixed[][] $arguments
     */
    public static function withArgs(callable $function, iterable $arguments): void
    {
        foreach ($arguments as $args) {
            $function(...$args);
        }
    }

}
