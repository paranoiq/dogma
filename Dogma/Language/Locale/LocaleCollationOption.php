<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Language\Locale;

interface LocaleCollationOption
{

    /**
     * @param string $value
     * @return \Dogma\Language\Locale\LocaleCollationOption
     */
    public static function get($value);

    public function getCollatorValue(): int;

}
