<?php

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
