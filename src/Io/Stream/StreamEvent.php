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
use const STREAM_NOTIFY_AUTH_REQUIRED;
use const STREAM_NOTIFY_AUTH_RESULT;
use const STREAM_NOTIFY_COMPLETED;
use const STREAM_NOTIFY_CONNECT;
use const STREAM_NOTIFY_FAILURE;
use const STREAM_NOTIFY_FILE_SIZE_IS;
use const STREAM_NOTIFY_MIME_TYPE_IS;
use const STREAM_NOTIFY_PROGRESS;
use const STREAM_NOTIFY_REDIRECTED;
use const STREAM_NOTIFY_RESOLVE;

class StreamEvent
{
    use StaticClassMixin;

    public const RESOLVED = STREAM_NOTIFY_RESOLVE;
    public const CONNECTED = STREAM_NOTIFY_CONNECT;
    public const AUTH_REQUIRED = STREAM_NOTIFY_AUTH_REQUIRED;
    public const AUTH_RESULT = STREAM_NOTIFY_AUTH_RESULT;
    public const MIME_TYPE = STREAM_NOTIFY_MIME_TYPE_IS;
    public const FILE_SIZE = STREAM_NOTIFY_FILE_SIZE_IS;
    public const REDIRECTED = STREAM_NOTIFY_REDIRECTED;
    public const PROGRESS = STREAM_NOTIFY_PROGRESS;
    public const COMPLETED = STREAM_NOTIFY_COMPLETED;
    public const FAILURE = STREAM_NOTIFY_FAILURE;

}
