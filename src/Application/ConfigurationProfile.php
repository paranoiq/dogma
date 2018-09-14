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

final class ConfigurationProfile extends \stdClass
{
    use StrictBehaviorMixin;

    /** @var mixed[] */
    private $values;

    /**
     * @param mixed[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (isset($this->values[$name])) {
            return $this->values[$name];
        } else {
            return null;
        }
    }

}
