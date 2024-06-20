<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\Stream;

use StreamContext;

class HttpContext extends StreamContext
{
    use SslOptionsMixin;

    public function setUserAgent(string $agent): self
    {
        return $this->setOption('http', HttpOptions::USER_AGENT, $agent);
    }

    public function followLocation(bool $enabled = true, int $maxRedirects = 20): self
    {
        return $this->setOption('http', HttpOptions::FOLLOW_LOCATION, $enabled)
            ->setOption('http', HttpOptions::MAX_REDIRECTS, $maxRedirects);
    }

    public function setTimeout(int $seconds = 60): self
    {
        return $this->setOption('http', HttpOptions::TIMEOUT, $seconds);
    }

}
