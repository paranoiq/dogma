<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Im;

use Dogma\Time\Date;

class ImAccount
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \Dogma\People\Im\ImAccountType */
    private $type;

    /** @var string */
    private $nickName;

    /** @var string */
    private $identifier;

    /** @var string|null */
    private $provider;

    /** @var \Dogma\Time\Date|null */
    private $createdTime;

    public function __construct(
        ImAccountType $type,
        string $nickName,
        string $identifier,
        ?string $provider = null,
        ?Date $createdDate = null
    )
    {
        $this->type = $type;
        $this->nickName = $nickName;
        $this->identifier = $identifier;
        $this->provider = $provider;
        $this->createdTime = $createdDate;
    }

}
