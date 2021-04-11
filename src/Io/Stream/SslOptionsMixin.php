<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Stream;

use Dogma\Io\IoException;
use function error_clear_last;
use function error_get_last;
use function stream_context_set_option;

/**
 * @property resource $context
 */
trait SslOptionsMixin
{

    /**
     * @param bool[]|int[]|string[]|string[][] $options
     * @return self
     */
    public function setSslOptions(array $options): self
    {
        error_clear_last();
        $res = @stream_context_set_option($this->context, ['ssl' => $options]);
        if ($res === false) {
            throw new IoException("Cannot set stream SSL/TLS options on wrapper: " . error_get_last()['message']);
        }

        return $this;
    }

}
