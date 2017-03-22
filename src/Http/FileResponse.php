<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Http;

use Dogma\Io\File;

class FileResponse extends \Dogma\Http\Response
{

    /** @var \Dogma\Io\File */
    private $file;

    /**
     * @param \Dogma\Http\ResponseStatus $status
     * @param \Dogma\Io\File $file
     * @param string[] $rawHeaders
     * @param string[] $info
     * @param mixed $context
     * @param \Dogma\Http\HeaderParser $headerParser
     */
    public function __construct(ResponseStatus $status, File $file, array $rawHeaders, array $info, $context, ?HeaderParser $headerParser = null)
    {
        parent::__construct($status, null, $rawHeaders, $info, $context, $headerParser);

        $this->file = $file;
    }

    public function getFile(): File
    {
        return $this->file;
    }

}
