<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System\Error;

use const PHP_OS;
use function is_int;
use function strpos;
use function strtolower;

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
     * @return \Dogma\System\Error\SystemError|null
     */
    public static function getError(int $errno, $system = self::LOCAL): ?SystemError
    {
        if (!$system || !is_int($system)) {
            $system = self::detectSystem($system);
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
        } catch (\Throwable $e) {
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

    public static function detectSystem(?string $string = null): ?int
    {
        if (!$string) {
            $string = PHP_OS;
        }
        $string = strtolower($string);

        if (strpos($string, 'linux') !== false) {
            return self::LINUX;
        } elseif (strpos($string, 'win') !== false) {
            return self::WINDOWS;
        } elseif (strpos($string, 'mac') !== false) {
            return self::UNIX;
        } elseif (strpos($string, 'bsd') !== false) {
            return self::UNIX;
        } elseif (strpos($string, 'unix') !== false) {
            return self::UNIX;
        }

        return null;
    }

}
