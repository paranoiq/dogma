<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Application;

use Dogma\StrictBehaviorMixin;
use stdClass;

final class ConfigurationProfile extends stdClass
{
    use StrictBehaviorMixin;

    /** @var array<mixed> */
    private array $values;

    /**
     * @param array<mixed> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function __get(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

}
