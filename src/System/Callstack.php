<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\StrictBehaviorMixin;
use const DEBUG_BACKTRACE_PROVIDE_OBJECT;
use function debug_backtrace;

class Callstack
{
    use StrictBehaviorMixin;

    /** @var CallstackFrame[] */
    public $frames;

    /**
     * @param CallstackFrame[] $frames
     */
    public function __construct(array $frames)
    {
        $this->frames = $frames;
    }

    public static function get(): self
    {
        $backtrace = debug_backtrace();
        $frames = [];
        foreach ($backtrace as $i => $item) {
            if ($i === 0) {
                continue;
            }
            $frames[] = new CallstackFrame($item);
        }

        return new self($frames);
    }

    public static function last(): CallstackFrame
    {
        $item = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

        return new CallstackFrame($item);
    }

    public static function previous(): CallstackFrame
    {
        $item = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)[2];

        return new CallstackFrame($item);
    }

}
