<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class Sign
{
    use \Dogma\StaticClassMixin;

    const SIGNED = 'signed';
    const UNSIGNED = 'unsigned';

    const POSITIVE = 1;
    const NEUTRAL = 0;
    const NEGATIVE = -1;

}
