<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;


class ErrorHelper {

    const
        LOCAL = 0,
        LINUX = 1,
        UNIX  = 2,
        WINDOWS = 3;


    /**
     * Get error object for given error number.
     * @param int|string
     * @param string
     * @return Error  (FALSE if not found)
     */
    public static function getError($errno, $system = self::LOCAL) {
        if (!$system || !is_int($system)) $system = self::detectSystem($system);
        if (!$system) return FALSE;

        try {
            switch ($system) {
                case self::LINUX:
                    return LinuxError::getInstance($errno);
                case self::UNIX:
                    return UnixError::getInstance($errno);
                case self::WINDOWS:
                    return WindowsError::getInstance($errno);
            }
        } catch (\Exception $e) {
            return FALSE;
        }

        return FALSE;
    }


    /**
     * Get error message for given error number.
     * @param int
     * @param string
     * @return string  (FALSE if not found)
     */
    public static function getErrorDescription($errno, $system = self::LOCAL) {
        if ($error = self::getError($errno, $system)) {
            return $error->getDescription();
        }

        return FALSE;
    }


    /**
     * Detect underlying operation system family.
     * @param string
     * @return string
     */
    public static function detectSystem($string = NULL) {
        if (!$string) $string = PHP_OS;
        $string = strtolower($string);

        if (strpos($string, 'linux') !== FALSE) {
            return self::LINUX;
        } elseif (strpos($string, 'win') !== FALSE) {
            return self::WINDOWS;
        } elseif (strpos($string, 'mac') !== FALSE) {
            return self::UNIX;
        } elseif (strpos($string, 'bsd') !== FALSE) {
            return self::UNIX;
        } elseif (strpos($string, 'unix') !== FALSE) {
            return self::UNIX;
        }

        return FALSE;
    }

}
