<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\People\Person;

class Generation extends \Dogma\Enum\IntEnum
{

    public const YOUNGER = 1;
    public const OLDER = 2;
    public const YOUNGEST = 3;
    public const OLDEST = 4;

}
