<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


class DogmaLoader extends \Nette\Loaders\AutoLoader {
    
    /** @var static */
    private static $instance;
    

    /** @var array */
    public $list = array(
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

        'Dogma\\Io\\IoException' => '/Io/exceptions',
        'Dogma\\Io\\FileException' => '/Io/exceptions',
        'Dogma\\Io\\DirectoryException' => '/Io/exceptions',
        'Dogma\\Io\\StreamException' => '/Io/exceptions',
    );



    /**
     * Returns singleton instance with lazy instantiation.
     * @return static
     */
    public static function getInstance() {
        if (self::$instance === NULL) {
            self::$instance = new static;
        }
        return self::$instance;
    }



    /**
     * Handles autoloading of classes or interfaces.
     * @param  string
     */
    public function tryLoad($type) {
        $type = ltrim($type, '\\');
        if (isset($this->list[$type])) {
            \Nette\Utils\LimitedScope::load(DOGMA_DIR . $this->list[$type] . '.php');
            self::$count++;
            
        } elseif (substr($type, 0, 6) === 'Dogma\\') {
            \Nette\Utils\LimitedScope::load(DOGMA_DIR . strtr(substr($type, 5), '\\', '/') . '.php');
            self::$count++;
        }
    }

}
