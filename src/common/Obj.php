<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint

namespace Dogma;

use function function_exists;
use function hexdec;
use function is_object;
use function md5;
use function spl_object_hash;
use function substr;

class Obj
{
    use StaticClassMixin;

    /**
     * @param object $object
     */
    public static function dumpHash($object): string
    {
        return substr(md5(spl_object_hash($object)), 0, 4);
    }

    /**
     * @param object|resource $object
     * @return int
     */
    public static function objectId($object): int
    {
        if (is_object($object)) {
            // PHP >= 7.2
            if (function_exists('spl_object_id')) {
                return spl_object_id($object);
            } else {
                $hash = spl_object_hash($object);
                $hash = substr($hash, 8, 8) . substr($hash, 24, 8);

                return (int) hexdec($hash);
            }
        } else {
            return (int) $object;
        }
    }

}
