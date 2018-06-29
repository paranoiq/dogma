<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Interval;

use Dogma\StrictBehaviorMixin;
use Dogma\Time\InvalidFormattingStringException;

class SmartDateTimeIntervalFormatter implements DateTimeIntervalFormatter
{
    use StrictBehaviorMixin;

    /**
     * "d. n*({ Y})[ H:i]|{ - d. n*( Y)[ H:i]}"
     * "d. m.({ Y}) h:I| - h:I"
     *
     * Ch.  Description
     * ---- ---------------------------------------
     * Escaping:
     * %    Escape character. Use %% for printing "%"
     *
     * Interval:
     * |    Logical separator of since and until date. Not printed in result
     *
     * Skip groups:
     * [    Group start, skip if zero
     * ]    Group end, skip if zero
     * (    Group start, skip if same as today
     * )    Group end, skip if same as today
     * {    Group start, skip if same for both
     * }    Group end, skip if same for both
     *
     * For modifiers and objects @see DateTimeFormatter::format()
     *
     * @param \Dogma\Time\Interval\DateOrTimeInterval $interval
     * @param string|null $format
     * @return string
     */
    public function format(DateOrTimeInterval $interval, ?string $format = null): string
    {
        $parts = explode(self::START_END_SEPARATOR, $format);
        if (count($parts) !== 2) {
            throw new InvalidFormattingStringException(
                sprintf('Format string "%s" should contain exactly one "|" separator, to distinguish format for since and until date.', $format)
            );
        }
        [$sinceFormat, $untilFormat] = $parts;

        ///
        return '';
    }

    /**
     * @param \Dogma\Time\Interval\DateTimeInterval|\Dogma\Time\Interval\DateInterval|\Dogma\Time\Interval\TimeInterval $interval
     * @return string
     */
    public function formatInterval($interval): string
    {
        ///
        return '';
    }

}
