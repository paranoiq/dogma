<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

use Closure;
use Dogma\Io\Io;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function rtrim;
use function strpos;

class Callback
{
    use StaticClassMixin;

    /**
     * Call given callback on another PHP binary
     * Must be a system function, closure or static method without dependencies and only with constant arguments.
     *
     * @param string $binary
     * @param callable $callback
     * @param mixed $args
     * @return array
     */
    public static function executeOn(string $binary, callable $callback, ...$args): array
    {
        // todo
    }

    public static function compileDashR(callable $callback, ...$args): string
    {
        // todo
    }

    public static function getBody(callable $callback): string
    {
        $ref = self::getReflection($callback);

        $file = $ref->getFileName();
        $start = $ref->getStartLine();
        $end = $ref->getEndLine();

        $definition = implode("\n", Io::readLines($file, null, $start - 1, $end - $start + 1));
        $pos = strpos($definition, 'function');
        $definition = substr($definition, $pos);

        return rtrim($definition, ';');
    }

    public static function getReflection(callable $callback): ReflectionFunctionAbstract
    {
        if (is_array($callback)) {
            return new ReflectionMethod(...$callback);
        } elseif (is_string($callback) && strpos($callback, '::') !== false) {
            return new ReflectionMethod(...explode('::', $callback));
        } elseif (is_string($callback) || $callback instanceof Closure) {
            return new ReflectionFunction($callback);
        } else {
            throw new ShouldNotHappenException('Invalid callback.');
        }
    }

}
