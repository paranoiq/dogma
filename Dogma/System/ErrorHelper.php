<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

class ErrorHelper
{

    const LOCAL = 0;
    const LINUX = 1;
    const UNIX = 2;
    const WINDOWS = 3;

    /**
     * Get error object for given error number.
     * @param int
     * @param int|string
     * @return \Dogma\System\Error|null
     */
    public static function getError(int $errno, $system = self::LOCAL)
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
     * @param int
     * @param string
     * @return string|null
     */
    public static function getErrorDescription(int $errno, $system = self::LOCAL)
    {
        if ($error = self::getError($errno, $system)) {
            return $error->getDescription();
        }

        return null;
    }

    /**
     * Detect underlying operation system family.
     * @param string $string
     * @return int|null
     */
    public static function detectSystem(string $string = null)
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
