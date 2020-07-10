<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System\Error;

use Dogma\System\Os;
use Throwable;
use const PHP_OS_FAMILY;
use function is_string;

class ErrorHelper
{

    public const LOCAL = 0;
    public const LINUX = 1;
    public const UNIX = 2;
    public const WINDOWS = 3;

    /**
     * Get error object for given error number.
     * @param int $errno
     * @param int|string $system
     * @return SystemError|null
     */
    public static function getError(int $errno, $system = self::LOCAL): ?SystemError
    {
        if ($system === self::LOCAL || is_string($system)) {
            $system = self::detectSystem();
        }
        if (!$system) {
            return null;
        }

        try {
            switch ($system) {
                case self::LINUX:
                    return LinuxError::get($errno);
                case self::UNIX:
                    return UnixError::get($errno);
                case self::WINDOWS:
                    return WindowsError::get($errno);
            }
        } catch (Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Get error message for given error number.
     * @param int $errno
     * @param int|string $system
     * @return string|null
     */
    public static function getErrorDescription(int $errno, $system = self::LOCAL): ?string
    {
        $error = self::getError($errno, $system);
        if ($error !== null) {
            return $error->getDescription();
        }

        return null;
    }

    private static function detectSystem(): ?int
    {
        if (PHP_OS_FAMILY === Os::LINUX) {
            return self::LINUX;
        } elseif (PHP_OS_FAMILY === Os::WINDOWS) {
            return self::WINDOWS;
        } elseif (PHP_OS_FAMILY === Os::BSD) {
            return self::UNIX;
        } elseif (PHP_OS_FAMILY === Os::SOLARIS) {
            return self::UNIX;
        } elseif (PHP_OS_FAMILY === Os::DARWIN) {
            return self::UNIX;
        }

        return null;
    }

}
