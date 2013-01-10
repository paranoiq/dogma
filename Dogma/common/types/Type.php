<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma;


/**
 * Common type enumeration
 */
final class Type extends Enum {

    const NULL = 'null';
    const BOOL = 'bool';
    const INT = 'int';
    const FLOAT = 'float';
    const STRING = 'string';

    const DATE = 'Date';
    const DATETIME = 'DateTime';

    const REGEXP = 'Regexp';

    const ENUM = 'Enum';
    const SET = 'Set';

}
