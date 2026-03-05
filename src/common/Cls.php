<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use function array_intersect;
use function array_pop;
use function array_search;
use function array_shift;
use function array_slice;
use function array_unshift;
use function array_values;
use function class_parents;
use function end;
use function explode;
use function is_object;

class Cls
{
    use StaticClassMixin;

    public static function short(string $class): string
    {
        $parts = explode('\\', $class);
        /** @var string $result */
        $result = end($parts);

        return $result;
    }

    /**
     * @return string[]
     */
    public static function parents(string|object $class): array
    {
        $parents = class_parents($class);
        if ($parents === false) {
            throw new InvalidValueException($class, 'class name or object');
        }

        return array_values($parents);
    }

    /**
     * @return string[]
     */
    public static function parentsAndSelf(string|object $class): array
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        $parents = class_parents($class);
        if ($parents === false) {
            throw new InvalidValueException($class, 'class name or object');
        }

        $parents = array_values($parents);
        array_unshift($parents, $class);

        return $parents;
    }

    /**
     * Oldest common ancestor
     *
     * @return string|null
     */
    public static function commonRoot(string|object $first, string|object $second, string|object|null $after = null): ?string
    {
        static $commonRoots = [];

        if (is_object($first)) {
            $first = $first::class;
        }
        if (is_object($second)) {
            $second = $second::class;
        }
        if (is_object($after)) {
            $after = $after::class;
        }

        $root = $commonRoots[$first][$second][$after] ?? null;
        if ($root !== null) {
            return $root ?: null;
        }

        $firstParents = self::parentsAndSelf($first);
        $secondParents = self::parentsAndSelf($second);

        $common = array_intersect($firstParents, $secondParents);
        if ($common === []) {
            $commonRoots[$first][$second][(string) $after] = false;
            return null;
        }

        if ($after !== null) {
            $common = array_values($common);
            /** @var int $index */
            $index = array_search($after, $common, true);
            $common = array_slice($common, 0, $index);
            if ($common === []) {
                $commonRoots[$first][$second][$after] = false;
                return null;
            }
        }

        $root = array_pop($common);
        $commonRoots[$first][$second][(string) $after] = $root;

        return $root;
    }

    /**
     * Newest common ancestor
     *
     * @return string|null
     */
    public static function commonBranch(string|object $first, string|object $second, string|object|null $after = null): ?string
    {
        static $commonBranches = [];

        if (is_object($first)) {
            $first = $first::class;
        }
        if (is_object($second)) {
            $second = $second::class;
        }
        if (is_object($after)) {
            $after = $after::class;
        }

        $branch = $commonBranches[$first][$second][$after] ?? null;
        if ($branch !== null) {
            return $branch ?: null;
        }

        $firstParents = self::parentsAndSelf($first);
        $secondParents = self::parentsAndSelf($second);

        $common = array_intersect($firstParents, $secondParents);
        if ($common === []) {
            $commonBranches[$first][$second][(string) $after] = false;
            return null;
        }

        if ($after !== null) {
            $common = array_values($common);
            /** @var int $index */
            $index = array_search($after, $common, true);
            $common = array_slice($common, 0, $index);
            if ($common === []) {
                $commonBranches[$first][$second][$after] = false;
                return null;
            }
        }

        $branch = array_shift($common);
        $commonBranches[$first][$second][(string) $after] = $branch;

        return $branch;
    }

}
