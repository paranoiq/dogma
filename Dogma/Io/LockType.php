<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

class LockType extends \Dogma\Enum
{

    public const SHARED = LOCK_SH;
    public const EXCLUSIVE = LOCK_EX;
    public const NON_BLOCKING = LOCK_NB;

}
