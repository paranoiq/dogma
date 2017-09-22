<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Entity\Map\Versioned;

use Dogma\Check;
use Dogma\Entity\Identity;

class VersionedIdentityMap extends \Dogma\Entity\Map\IdentityMap implements \Dogma\Transaction\VersionAware
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\Entity\Identity[][] ($versionId => ($id => $identity)) */
    private $versionMap = [];

    /** @var int */
    private $versionId = 0;

    public function getVersionId(): int
    {
        return $this->versionId;
    }

    public function incrementVersion(int $versionId): void
    {
        Check::range($versionId, $this->versionId + 1);

        $this->versionId = $versionId;
    }

    public function releaseToVersion(int $versionId): void
    {
        Check::range($versionId, 0, $this->versionId - 1);

        // should do a refcount garbage collection, but there is no way to implement it :[
        // scan all Entities instead? (not completely safe)
        ///
    }

    public function rollbackToVersion(int $versionId): void
    {
        Check::range($versionId, 0, $this->versionId - 1);

        // clean new identities
        foreach ($this->versionMap as $vid => $items) {
            if ($vid <= $versionId) {
                continue;
            }
            foreach ($items as $id => $identity) {
                $className = get_class($identity);
                unset($this->idMap[$className][$identity->getId()]);
            }
            unset($this->versionMap[$vid]);
        }

        $this->versionId = $versionId;
    }

    public function add(Identity $identity): void
    {
        if (!$this->versionId) {
            throw new \Dogma\Transaction\VersioningNotInitializedException($this);
        }

        parent::add($identity);

        $this->versionMap[$this->versionId][$identity->getId()] = $identity;
    }

}
