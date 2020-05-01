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
use function get_class;
use function is_object;

class Cls
{
    use StaticClassMixin;

    /**
     * @param string|object $class
     * @return string[]
     */
    public static function parents($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        return array_values(class_parents($class));
    }

    /**
     * @param string|object $class
     * @return string[]
     */
    public static function parentsAndSelf($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $parents = array_values(class_parents($class));
        array_unshift($parents, $class);

        return $parents;
    }

    /**
     * Oldest common ancestor
     *
     * @param string|object $first
     * @param string|object $second
     * @param string|object|null $after
     * @return string|null
     */
    public static function commonRoot($first, $second, $after = null): ?string
    {
        static $commonRoots = [];

        if (is_object($first)) {
            $first = get_class($first);
        }
        if (is_object($second)) {
            $second = get_class($second);
        }
        if (is_object($after)) {
            $after = get_class($after);
        }

        $root = $commonRoots[$first][$second][$after] ?? null;
        if ($root !== null) {
            return $root ?: null;
        }

        $firstParents = self::parentsAndSelf($first);
        $secondParents = self::parentsAndSelf($second);

        $common = array_intersect($firstParents, $secondParents);
        if ($common === []) {
            $commonRoots[$first][$second][$after] = false;
            return null;
        }

        if ($after !== null) {
            $common = array_values($common);
            $index = array_search($after, $common, true);
            $common = array_slice($common, 0, $index);
            if ($common === []) {
                $commonRoots[$first][$second][$after] = false;
                return null;
            }
        }

        $root = array_pop($common);
        $commonRoots[$first][$second][$after] = $root;

        return $root;
    }

    /**
     * Newest common ancestor
     *
     * @param string|object $first
     * @param string|object $second
     * @param string|object|null $after
     * @return string|null
     */
    public static function commonBranch($first, $second, $after = null): ?string
    {
        static $commonBranches = [];

        if (is_object($first)) {
            $first = get_class($first);
        }
        if (is_object($second)) {
            $second = get_class($second);
        }
        if (is_object($after)) {
            $after = get_class($after);
        }

        $branch = $commonBranches[$first][$second][$after] ?? null;
        if ($branch !== null) {
            return $branch ?: null;
        }

        $firstParents = self::parentsAndSelf($first);
        $secondParents = self::parentsAndSelf($second);

        $common = array_intersect($firstParents, $secondParents);
        if ($common === []) {
            $commonBranches[$first][$second][$after] = false;
            return null;
        }

        if ($after !== null) {
            $common = array_values($common);
            $index = array_search($after, $common, true);
            $common = array_slice($common, 0, $index);
            if ($common === []) {
                $commonBranches[$first][$second][$after] = false;
                return null;
            }
        }

        $branch = array_shift($common);
        $commonBranches[$first][$second][$after] = $branch;

        return $branch;
    }

}
