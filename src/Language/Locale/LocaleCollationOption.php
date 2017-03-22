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
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingReturnTypeHint
     * @param string|int $value
     * @return \Dogma\Language\Locale\LocaleCollationOption
     */
    public static function get($value); // compat with Enum

    public function getCollatorValue(): int;

}
