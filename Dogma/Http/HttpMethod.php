<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

class HttpMethod extends \Dogma\Enum
{

    const GET = 'get';
    const HEAD = 'head';
    const POST = 'post';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';
    const TRACE = 'trace';
    const OPTIONS = 'options';
    const CONNECT = 'connect';

}
