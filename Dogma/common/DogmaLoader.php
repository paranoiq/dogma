<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


final class DogmaLoader
{

    /** @var static */
    private static $instance;

    /** @var string[] */
    private $classMap = [];

    private function __construct()
    {
        $this->scan(__DIR__);
    }

    /**
     * Returns singleton instance with lazy instantiation
     * @return static
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * Register autoloader
     * @param bool
     * @return void
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'tryLoad'), true, (bool) $prepend);
    }

    /**
     * Handles autoloading of classes or interfaces.
     * @param string
     */
    public function tryLoad($class)
    {
        $class = ltrim($class, '\\');
        if (isset($this->classMap[$class])) {
            require $this->classMap[$class];
            return;
        }

        if (substr($class, 0, 6) !== 'Dogma\\') {
            return;
        }

        $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . strtr(substr($class, 5), '\\', DIRECTORY_SEPARATOR) . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }

        if (substr($class, -9) === 'Exception') {
            $parts = explode('\\', substr($class, 5));
            $last = array_pop($parts);
            $parts[] = 'exceptions';
            $parts[] = $last;
            $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * @param string $dir
     */
    private function scan($dir)
    {
        foreach (glob($dir . '\\*') as $path) {
            if (is_dir($path)) {
                $this->scan($path);
            } elseif (is_file($path)) {
                $parts = explode(DIRECTORY_SEPARATOR, str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path));
                $file = array_pop($parts);
                $class = substr($file, 0, -4);
                $this->classMap[sprintf('Dogma\\%s', $class)] = $path;
            }
        }
    }

}
