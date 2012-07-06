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
    
    const INT = 'int';
    const FLOAT = 'float';
    const STRING = 'string';
    const BOOL = 'bool';
    const NULL = 'null';
    
    const DATE = 'date';
    const DATETIME = 'datetime';
    
    //const REGEXP = 'string';
    
    //const ENUM = 'enum';
    
}
