<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use StreamContext;
use Throwable;

class FilesystemException extends IoException
{

    public const DIRECTORY_DOES_NOT_EXIST = 1;
    public const DIRECTORY_ALREADY_EXISTS = 2;
    public const FILE_DOES_NOT_EXIST = 3;
    public const FILE_ALREADY_EXIST = 4;
    public const WRONG_FILE_MODE = 5;
    public const NOT_AUTHORIZED = 6;
    public const LOCKED = 7;
    public const CANNOT_ACQUIRE_LOCK = 8;

    /** @var string|null */
    private $path;

    /** @var StreamContext|null */
    private $context;

    /** @var mixed[]|null */
    private $error;

    /** @var int */
    private $reason;

    /**
     * @param string $message
     * @param string|null $path
     * @param StreamContext|null $context
     * @param mixed[]|null $error
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        ?string $path,
        ?StreamContext $context = null,
        ?array $error = null,
        ?Throwable $previous = null
    ) {
        if ($path !== null) {
            $message .= ': ' . $path;
        }

        parent::__construct($message, $previous);

        $this->path = $path;
        $this->context = $context;
        $this->error = $error;
    }

    /**
     * @param string $message
     * @param string|null $path
     * @param StreamContext|null $context
     * @param mixed[]|null $error
     * @param Throwable|null $previous
     * @return self
     */
    public static function create(
        string $message,
        ?string $path,
        ?StreamContext $context = null,
        ?array $error = null,
        ?Throwable $previous = null
    ): self
    {
        // todo permissions, locking

        return new self($message, $path, $context, $error, $previous);
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getContext(): ?StreamContext
    {
        return $this->context;
    }

    /**
     * @return mixed[]|null
     */
    public function getError(): ?array
    {
        return $this->error;
    }

}
