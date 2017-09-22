<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Database\Charset;

class MysqlCollationType extends \Dogma\Enum\StringEnum
{

    public const LOCALE_CI = 'ci';
    public const UNICODE_CI = 'unicode_520_ci';
    public const GENERAL_CI = 'general_ci';
    public const BINARY = 'bin';

    public const OLD_GENERAL_CI = 'general_mysql500_ci';
    public const OLD_UNICODE_CI = 'unicode_ci';

}
