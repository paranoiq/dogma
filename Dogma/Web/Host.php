<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Web;

use Dogma\Check;
use Dogma\Str;

class Host
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string */
    private $host;

    /** @var int|null */
    private $port;

    public function __construct(string $host, int $port = null)
    {
        if ($port === null && Str::contains($host, ':')) {
            list($host, $port) = Str::splitByFirst($host, ':');
        }
        Check::nullableInt($port, 0, 65536);

        $this->host = $host;
        $this->port = $port;
    }

    public function getTld(): Tld
    {
        $parts = explode('.', $this->host);

        return Tld::get(end($parts));
    }

}
