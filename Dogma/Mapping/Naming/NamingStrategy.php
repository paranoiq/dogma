<?php

namespace Dogma\Mapping\Naming;

interface NamingStrategy
{
    
    public function translateName(string $localName, string $path, string $fieldSeparator): string;

}
