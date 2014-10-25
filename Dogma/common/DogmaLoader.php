<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


class DogmaLoader {

    /** @var static */
    private static $instance;

    /** @var string[] */
    public $list = [
        'Dogma\\CompoundValueObject' => '/common/interfaces/CompoundValueObject',
        'Dogma\\IndirectInstantiable' => '/common/interfaces/IndirectInstantiable',
        'Dogma\\SimpleValueObject' => '/common/interfaces/SimpleValueObject',
        'Dogma\\ValueObject' => '/common/interfaces/ValueObject',

        'Dogma\\ArrayObject' => '/common/types/ArrayObject',
        'Dogma\\Collection' => '/common/types/Collection',
        'Dogma\\Date' => '/common/types/Date',
        'Dogma\\DateTime' => '/common/types/DateTime',
        'Dogma\\Enum' => '/common/types/Enum',
        'Dogma\\Object' => '/common/types/Object',
        'Dogma\\Set' => '/common/types/Set',
        'Dogma\\Regexp' => '/common/types/Regexp',
        'Dogma\\String' => '/common/types/String',
        'Dogma\\Type' => '/common/types/Type',

        'Dogma\\Normalizer' => '/common/Normalizer',

        'Dogma\\Http\\HttpException' => '/Http/exceptions',
        'Dogma\\Http\\ChannelException' => '/Http/exceptions',
        'Dogma\\Http\\RequestException' => '/Http/exceptions',
        'Dogma\\Http\\ResponseException' => '/Http/exceptions',

        'Dogma\\Io\\IoException' => '/Io/exceptions',
        'Dogma\\Io\\FileException' => '/Io/exceptions',
        'Dogma\\Io\\DirectoryException' => '/Io/exceptions',
        'Dogma\\Io\\StreamException' => '/Io/exceptions',
    ];

    /**
     * Returns singleton instance with lazy instantiation
     * @return static
     */
    public static function getInstance() {
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
    public function register($prepend = FALSE)
    {
        spl_autoload_register(array($this, 'tryLoad'), TRUE, (bool) $prepend);
    }

    /**
     * Handles autoloading of classes or interfaces.
     * @param  string
     * @return void
     */
    public function tryLoad($type)
    {
        $type = ltrim($type, '\\');
        if (isset($this->list[$type])) {
            require __DIR__ . '/../' . $this->list[$type] . '.php';

        } elseif (substr($type, 0, 6) === 'Dogma\\' && is_file($file = __DIR__ . '/../' . strtr(substr($type, 5), '\\', '/') . '.php')) {
            require $file;
        }
    }

}
