<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity;

use Dogma\Check;
use Dogma\Entity\Map\IdentityMap;
use Dogma\Type;

/**
 * Identity
 */
abstract class Identity
{
    use \Dogma\StrictBehaviorMixin;
    use \Dogma\NonCloneableMixin;
    use \Dogma\NonIterableMixin;
    use \Dogma\NonSerializableMixin;

    /** @var int|string */
    private $id;

    /**
     * @param int|string $id
     */
    final private function __construct($id)
    {
        Check::types($id, [Type::INT, Type::STRING], 1);

        $this->id = $id;
    }

    /**
     * @param \Dogma\Entity\Map\IdentityMap $identityMap
     * @param int|string $id
     * @return \Dogma\Entity\Identity
     */
    final public static function get(IdentityMap $identityMap, $id): self
    {
        $className = get_called_class();
        $identity = $identityMap->findById($className, $id);
        if ($identity !== null) {
            return $identity;
        }

        $identity = new static($id);
        $identityMap->add($identity);

        return $identity;
    }

    final public function getId(): int
    {
        return $this->id;
    }

}
