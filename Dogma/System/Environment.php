<?php

namespace Dogma\System;

class Environment
{
    use \Dogma\StaticClassMixin;

    public static function isWindows(): bool
    {
        return strstr(strtolower(PHP_OS), 'win');
    }

}
