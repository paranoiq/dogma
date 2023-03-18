<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Io\BinaryFile;
use Dogma\Io\FileInfo;
use Dogma\Io\Io;
use Dogma\LogicException;
use Dogma\Str;
use Dogma\StrictBehaviorMixin;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use function implode;

class CallstackFrame
{
    use StrictBehaviorMixin;

    public const INSTANCE = '->';
    public const STATIC = '::';

    /** @var string|null */
    public $file;

    /** @var int|null */
    public $line;

    /** @var string|null */
    public $class;

    /** @var object|null */
    public $object;

    /** @var string */
    public $function;

    /** @var mixed[] */
    public $args;

    /** @var string self::INSTANCE | self::STATIC */
    public $type;

    /**
     * @param mixed[] $data
     */
    public function __construct(array $data)
    {
        $this->file = isset($data['file']) ? Io::normalizePath($data['file']) : null;
        $this->line = $data['line'] ?? null;
        $this->class = $data['class'] ?? null;
        $this->object = $data['object'] ?? null;
        $this->function = $data['function'] ?? null;
        $this->args = $data['args'] ?? null;
        $this->type = $data['type'] ?? null;
    }

    public function getFullName(): string
    {
        return $this->class ? $this->class . $this->type . $this->function : $this->function;
    }

    public function isClosure(): bool
    {
        return $this->class === null && Str::endsWith($this->function, '{closure}');
    }

    public function isFunction(): bool
    {
        return $this->class === null && !Str::endsWith($this->function, '{closure}');
    }

    public function isMethod(): bool
    {
        return $this->class !== null;
    }

    public function isStatic(): bool
    {
        return $this->type === self::STATIC;
    }

    public function isAnonymous(): bool
    {
        return $this->class !== null && Str::startsWith($this->class, 'class@anonymous');
    }

    // reflection ------------------------------------------------------------------------------------------------------

    public function getFunctionReflection(): ReflectionFunction
    {
        if (!$this->isFunction()) {
            throw new LogicException($this->getFullName() . ' is not a function.');
        }

        return new ReflectionFunction($this->function);
    }

    public function getMethodReflection(): ReflectionMethod
    {
        if (!$this->isMethod()) {
            throw new LogicException($this->getFullName() . ' is not a method.');
        }
        /** @var string $object */
        $object = $this->object ?? $this->class;

        return new ReflectionMethod($object, $this->function);
    }

    public function getObjectReflection(): ReflectionObject
    {
        if ($this->object === null) {
            throw new LogicException($this->getFullName() . ' is not an instance method.');
        }

        return new ReflectionObject($this->object);
    }

    public function getClassReflection(): ReflectionClass
    {
        if ($this->class === null) {
            throw new LogicException($this->getFullName() . ' is not a class method.');
        }

        return new ReflectionClass($this->class);
    }

    // code ------------------------------------------------------------------------------------------------------------

    public function getLineCode(): string
    {
        if ($this->file === null) {
            throw new LogicException($this->getFullName() . ' does not have a file.');
        }

        return Io::readLines($this->file, null, $this->line - 1, 1)[0];
    }

    public function getCode(): string
    {
        if ($this->file === null) {
            throw new LogicException($this->getFullName() . ' does not have a file.');
        }

        if ($this->isFunction()) {
            $reflection = $this->getFunctionReflection();
        } elseif ($this->isMethod()) {
            $reflection = $this->getMethodReflection();
        } else {
            throw new LogicException('Cannot get code of a closure.');
        }
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        return implode("\n", Io::readLines($this->file, null, $start - 1, $end - $start + 1));
    }

    public function getClassCode(): string
    {
        if ($this->file === null) {
            throw new LogicException($this->getFullName() . ' does not have a file.');
        }

        if ($this->isMethod()) {
            $reflection = $this->getClassReflection();
        } else {
            throw new LogicException($this->getFullName() . ' is not a class method.');
        }
        $start = $reflection->getStartLine();
        $end = $reflection->getEndLine();

        return implode("\n", Io::readLines($this->file, null, $start - 1, $end - $start + 1));
    }

    public function getFileCode(): string
    {
        if ($this->file === null) {
            throw new LogicException($this->getFullName() . ' does not have a file.');
        }

        return Io::read($this->file);
    }

    public function getFile(): BinaryFile
    {
        if ($this->file === null) {
            throw new LogicException($this->getFullName() . ' does not have a file.');
        }

        return new BinaryFile($this->file);
    }

    public function getFileInfo(): FileInfo
    {
        if ($this->file === null) {
            throw new LogicException($this->getFullName() . ' does not have a file.');
        }

        return new FileInfo($this->file);
    }

}
