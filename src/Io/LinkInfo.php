<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use function error_clear_last;
use function error_get_last;
use function lchgrp;
use function lchown;
use function lstat;
use function readlink;

/**
 * Represents a filesystem path to a symbolic link (not it's target)
 * Does not validate existence of the path
 */
class LinkInfo extends FileInfo
{

    /**
     * @return mixed[]
     */
    protected function stat(): array
    {
        error_clear_last();
        $stat = lstat($this->path);

        if ($stat === false) {
            throw FilesystemException::create("Cannot acquire file metadata", $this->path, null, error_get_last());
        }

        return $stat;
    }

    public function updateLinkOwner(int $owner, ?int $group = null): void
    {
        if (!$this->isLink()) {
            throw FilesystemException::create("Path is not a link", $this->path);
        }

        error_clear_last();
        $result = lchown($this->path, $owner);

        if ($result === false) {
            throw FilesystemException::create("Cannot change link owner", $this->path, null, error_get_last());
        }

        if ($group === null) {
            return;
        }

        error_clear_last();
        $result = lchgrp($this->path, $group);

        if ($result === false) {
            throw FilesystemException::create("Cannot change link group", $this->path, null, error_get_last());
        }
    }

    public function getLinkTarget(): FileInfo
    {
        if (!$this->isLink()) {
            throw FilesystemException::create("Path is not a link", $this->path);
        }

        error_clear_last();
        $target = readlink($this->path);
        if ($target === false) {
            throw FilesystemException::create("Cannot read link target", $this->path, null, error_get_last());
        }

        return new FileInfo($target);
    }

}
