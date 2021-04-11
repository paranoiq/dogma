<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Stream;

use Dogma\StaticClassMixin;
use const STREAM_NOTIFY_SEVERITY_ERR;
use const STREAM_NOTIFY_SEVERITY_INFO;
use const STREAM_NOTIFY_SEVERITY_WARN;

class StreamEventSeverity
{
    use StaticClassMixin;

    public const INFO = STREAM_NOTIFY_SEVERITY_INFO;
    public const WARNING = STREAM_NOTIFY_SEVERITY_WARN;
    public const ERROR = STREAM_NOTIFY_SEVERITY_ERR;

}
