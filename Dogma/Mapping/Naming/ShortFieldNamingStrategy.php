<?php

namespace Dogma\Mapping\Naming;

class ShortFieldNamingStrategy implements \Dogma\Mapping\Naming\NamingStrategy
{
    use \Dogma\StrictBehaviorMixin;
    
    public function translateName(string $localName, string $path, string $fieldSeparator): string
    {
        $parts = explode($fieldSeparator, $localName);

        return end($parts);
    }

}
