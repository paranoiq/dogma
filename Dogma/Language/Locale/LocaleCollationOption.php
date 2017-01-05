<?php

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
