<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\System;

use Dogma\Arr;

class Sapi extends \Dogma\Enum
{

    const AOL_SERVER = 'aolserver';
    const APACHE = 'apache';
    const APACHE_2_FILTER = 'apache2filter';
    const APACHE_2_HANDLER = 'apache2handler';
    const CAUDIUM = 'caudium';
    const CGI = 'cgi'; // (until PHP 5.3)
    const CGI_FCGI = 'cgi-fcgi';
    const CLI = 'cli';
    const CLI_SERVER = 'cli-server';
    const CONTINUITY = 'continuity';
    const EMBED = 'embed';
    const FPM_FCGI = 'fpm-fcgi';
    const ISAPI = 'isapi';
    const LITESPEED = 'litespeed';
    const MILTER = 'milter';
    const NSAPI = 'nsapi';
    const PHTTPD = 'phttpd';
    const PI3WEB = 'pi3web';
    const ROXEN = 'roxen';
    const THTTPD = 'thttpd';
    const TUX = 'tux';
    const WEBJAMES = 'webjames';

    private static $multithreaded = [
        self::AOL_SERVER,
        self::APACHE,
        self::APACHE_2_FILTER,
        self::APACHE_2_HANDLER,
        self::CAUDIUM,
        self::CONTINUITY,
        self::ISAPI,
        self::LITESPEED,
        self::MILTER,
        self::NSAPI,
        self::PHTTPD,
        self::PI3WEB,
        self::ROXEN,
        self::THTTPD,
        self::TUX,
        self::WEBJAMES,
    ];

    public function isMultithreaded(): bool
    {
        return Arr::contains(self::$multithreaded, $this->getValue());
    }

}
