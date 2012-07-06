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
        'Dogma\\Object' => '/common/types/Object.php',
        'Dogma\\ArrayObject' => '/common/types/ArrayObject.php',
        'Dogma\\Collection' => '/common/types/Collection.php',
        'Dogma\\DateTime' => '/common/types/DateTime.php',
        'Dogma\\Date' => '/common/types/Date.php',
        'Dogma\\Enum' => '/common/types/Enum.php',
        'Dogma\\Regexp' => '/common/types/Regexp.php',
        'Dogma\\String' => '/common/types/String.php',
        'Dogma\\Type' => '/common/types/Type.php',

        'Dogma\\Io\\IoException' => '/Io/exceptions.php',
        'Dogma\\Io\\FileException' => '/Io/exceptions.php',
        'Dogma\\Io\\DirectoryException' => '/Io/exceptions.php',
        'Dogma\\Io\\StreamException' => '/Io/exceptions.php',
        
        'Dogma\\Mail\\MailParserException' => '/Mail/exceptions.php',

        'Dogma\\Dom\\DomException' => '/Dom/exceptions.php',
        'Dogma\\Dom\\QueryEngineException' => '/Dom/exceptions.php',
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
            \Nette\Utils\LimitedScope::load(DOGMA_DIR . $this->list[$type] . '.php', TRUE);
            self::$count++;
            
        } elseif (substr($type, 0, 6) === 'Dogma\\') {
            \Nette\Utils\LimitedScope::load(DOGMA_DIR . strtr(substr($type, 5), '\\', '/') . '.php', TRUE);
            self::$count++;
        }
    }

}
