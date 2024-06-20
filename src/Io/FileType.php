<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// phpcs:disable Squiz.WhiteSpace.OperatorSpacing.SpacingBefore

namespace Dogma\Io;

use Dogma\StaticClassMixin;

class FileType
{
    use StaticClassMixin;

    public const PIPE         = 0010000;
    public const CHAR_DEVICE  = 0020000;
    public const DIRECTORY    = 0040000;
    public const BLOCK_DEVICE = 0060000;
    public const FILE         = 0100000;
    public const LINK         = 0120000;
    public const SOCKET       = 0140000;

    public const LETTERS = [
        self::PIPE => 'p',
        self::CHAR_DEVICE => 'c',
        self::DIRECTORY => 'd',
        self::BLOCK_DEVICE => 'b',
        self::FILE => '-',
        self::LINK => 'l',
        self::SOCKET => 's',
    ];

}
