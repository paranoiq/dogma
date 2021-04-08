<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application;

use ReflectionClass;

/**
 * Parser and loader for program configuration and renderer of cli program help screen
 *
 * This class loads arguments from command line (highest priority) and config files in .ini, .json or .neon formats.
 * Specific profiles from config files can be loaded, as given by default by property `use`.
 * Parsed data are filled into descendant class properties. These can be defined as public or protected (if you provide getters).
 */
abstract class Configuration
{

    private $properties;

    /**
     * Returns array with following structure, describing structure of help screen and other details:
     * string $category =>
     *   string $propertyName =>
     *     [char(0|1) $shortcut, string $description, ?string $argumentDescription, ?mixed $defaultValue, ?callable $validator]
     *
     * For example:
     * [
     *   'Configuration' => [
     *     '--config' =>       ['c', 'configuration files', 'paths', __DIR__ . '/build/spell-checker.neon'],
     *     '--memoryLimit' =>  ['m', 'memory limit', 'bytes'],
     *     ...
     *   ]
     * ]
     *
     * will be rendered as:
     * Configuration:
     *   -c --config <paths>       configuration files; default: C:/.../build/spell-checker.neon
     *   -m --memoryLimit <bytes>  memory limit
     *   ...
     *
     * @return array
     */
    abstract protected function getPropertyData(): array;

    public function commandPropertyName(): string
    {
        return 'command';
    }

    public function configPropertyName(): string
    {
        return 'config';
    }

    public function profilePropertyName(): string
    {
        return 'use';
    }

    public function load(): void
    {
        $r = new ReflectionClass($this);
        $properties = $r->getProperties();
        foreach ($properties as $property) {

        }
    }

    private function parseCommandLine(string $input)
    {

    }

}
