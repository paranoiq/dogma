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

/**
 * File download request.
 */
class DownloadRequest extends Request
{

    /** @var \Dogma\Io\File */
    private $file;

    /**
     * @return \Dogma\Http\FileResponse
     */
    public function execute(): Response
    {
        return parent::execute();
    }

    /**
     * Called by Channel.
     * @internal
     */
    public function prepare(): void
    {
        parent::prepare();

        $this->file = File::createTemporaryFile();

        $this->setOption(CURLOPT_FILE, $this->file->getHandle());
        $this->setOption(CURLOPT_BINARYTRANSFER, true);
    }

    /**
     * Called by Channel.
     * @internal
     *
     * @param string|bool $response
     * @param int $error
     * @return \Dogma\Http\FileResponse
     */
    public function createResponse($response, int $error): Response
    {
        $info = $this->getInfo();
        $status = $this->getResponseStatus($error, $info);

        return new FileResponse($status, $this->file, $this->responseHeaders, $info, $this->context, $this->headerParser);
    }

}
