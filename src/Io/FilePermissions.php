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

/**
 * Unix-style filesystem permissions
 *
 * @see https://en.wikipedia.org/wiki/File-system_permissions
 */
class FilePermissions
{
    use StaticClassMixin;

    public const NONE          = 0000;

    public const ALL           = 0777;
    public const ALL_READ      = 0444;
    public const ALL_WRITE     = 0222;
    public const ALL_EXECUTE   = 0111;

    public const OWNER_ALL     = 0700;
    public const OWNER_READ    = 0400;
    public const OWNER_WRITE   = 0200;
    public const OWNER_EXECUTE = 0100;

    public const GROUP_ALL     = 0070;
    public const GROUP_READ    = 0040;
    public const GROUP_WRITE   = 0020;
    public const GROUP_EXECUTE = 0010;

    public const OTHER_ALL     = 0007;
    public const OTHER_READ    = 0004;
    public const OTHER_WRITE   = 0002;
    public const OTHER_EXECUTE = 0001;

}
