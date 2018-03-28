<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Io\File;

class HttpFileResponse extends HttpResponse
{

    /** @var \Dogma\Io\File */
    private $file;

    /**
     * @param \Dogma\Http\HttpResponseStatus $status
     * @param \Dogma\Io\File $file
     * @param string[] $rawHeaders
     * @param string[] $info
     * @param mixed $context
     * @param \Dogma\Http\HttpHeaderParser $headerParser
     */
    public function __construct(HttpResponseStatus $status, File $file, array $rawHeaders, array $info, $context, ?HttpHeaderParser $headerParser = null)
    {
        parent::__construct($status, null, $rawHeaders, $info, $context, $headerParser);

        $this->file = $file;
    }

    public function getFile(): File
    {
        return $this->file;
    }

}
