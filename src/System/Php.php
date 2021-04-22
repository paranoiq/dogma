<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Io\Output;
use Dogma\StaticClassMixin;
use Dogma\Str;
use const INFO_GENERAL;
use const PHP_INT_SIZE;
use const PHP_SAPI;
use function error_clear_last;
use function error_get_last;
use function extension_loaded;
use function phpinfo;
use function proc_nice;

class Php
{
    use StaticClassMixin;

    public static function is32bit(): bool
    {
        return PHP_INT_SIZE < 8;
    }

    public static function is64bit(): bool
    {
        return PHP_INT_SIZE === 8;
    }

    public static function getSapi(): Sapi
    {
        return Sapi::get(PHP_SAPI);
    }

    public static function isMultithreaded(): bool
    {
        return self::getSapi()->isMultithreaded() || self::hasPthreads() || self::isThreadSafe();
    }

    public static function hasPthreads(): bool
    {
        return extension_loaded('pthreads');
    }

    public static function isThreadSafe(): bool
    {
        static $threadSafe;
        if ($threadSafe === null) {
            $info = Output::capture(function () {
                phpinfo(INFO_GENERAL);
            });
            $threadSafe = (bool) Str::match($info, '~Thread Safety\s*</td>\s*<td[^>]*>\s*enabled~');
        }

        return $threadSafe;
    }

    public static function setPriority(int $priority): void
    {
        error_clear_last();
        $result = @proc_nice($priority);
        if ($result !== true) {
            throw new CannotChangePriorityException('Cannot change system priority: ' . error_get_last()['message']);
        }
    }

}
