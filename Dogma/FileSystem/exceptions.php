<?php

namespace Dogma;


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

