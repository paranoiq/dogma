<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Mapping\Naming;

use Dogma\Language\Inflector;

class ConfigShortUnderscoreFieldNamingStrategy implements \Dogma\Mapping\Naming\NamingStrategy
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string[][] */
    private $keyMap;

    /**
     * @param string[][] $keyMap ($path => $handlerKey => $sourceKey)
     */
    public function __construct(array $keyMap)
    {
        $this->keyMap = $keyMap;
    }

    public function translateName(string $localName, string $path, string $fieldSeparator): string
    {
        $parts = explode($fieldSeparator, $localName);

        if (isset($this->keyMap[$path][$localName])) {
            return $this->keyMap[$path][$localName];
        }

        return Inflector::underscore(end($parts));
    }

}
