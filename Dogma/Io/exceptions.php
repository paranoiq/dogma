<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;


/**
 * Filesystem or stream exception
 */
class IoException extends \Nette\IOException {
    ///
}


class FileException extends IoException {
    ///
}


class DirectoryException extends IoException {
    ///
}


class StreamException extends IoException {
    ///
}

