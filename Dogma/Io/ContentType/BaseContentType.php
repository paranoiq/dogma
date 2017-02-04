<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io\ContentType;

class BaseContentType extends \Dogma\PartialEnum
{

    const APPLICATION = 'application';
    const AUDIO = 'audio';
    const FONT = 'font';
    const CHEMICAL = 'chemical';
    const IMAGE = 'image';
    const MESSAGE = 'message';
    const MODEL = 'model';
    const MULTIPART = 'multipart';
    const TEXT = 'text';
    const VIDEO = 'video';

    public static function getValueRegexp(): string
    {
        return '[a-z_]+';
    }

}
