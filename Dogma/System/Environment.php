<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

class Environment
{
    use \Dogma\StaticClassMixin;

    public static function isWindows(): bool
    {
        return strstr(strtolower(PHP_OS), 'win');
    }

}
