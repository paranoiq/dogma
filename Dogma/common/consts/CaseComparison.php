<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;

class CaseComparison
{
    use \Dogma\StaticClassMixin;

    const CASE_SENSITIVE = 0;
    const CASE_INSENSITIVE = SORT_FLAG_CASE; // 8

}
