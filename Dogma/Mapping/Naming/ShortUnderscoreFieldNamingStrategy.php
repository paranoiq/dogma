<?php

namespace Dogma\Mapping\Naming;

use Dogma\Language\Inflector;

class ShortUnderscoreFieldNamingStrategy implements NamingStrategy
{
    use \Dogma\StrictBehaviorMixin;
    
    public function translateName(string $localName, string $path, string $fieldSeparator): string
    {
        $parts = explode($fieldSeparator, $localName);

        return Inflector::underscore(end($parts));
    }

}
