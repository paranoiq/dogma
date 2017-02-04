<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

class FileMode
{
    use \Dogma\StaticClassMixin;

    // if not found: ERROR; keep content
    const OPEN_READ = 'rb';
    const OPEN_READ_WRITE = 'r+b';

    // if found: ERROR; no content
    const CREATE_WRITE = 'xb';
    const CREATE_READ_WRITE = 'x+b';

    // if not found: create; keep content
    const CREATE_OR_OPEN_WRITE = 'cb';
    const CREATE_OR_OPEN_READ_WRITE = 'c+b';

    // if not found: create; truncate content
    const CREATE_OR_TRUNCATE_WRITE = 'wb';
    const CREATE_OR_TRUNCATE_READ_WRITE = 'w+b';

    // if not found: create; keep content, point to end of file, don't accept new position
    const CREATE_OR_APPEND_WRITE = 'ab';
    const CREATE_OR_APPEND_READ_WRITE = 'a+b';

}
