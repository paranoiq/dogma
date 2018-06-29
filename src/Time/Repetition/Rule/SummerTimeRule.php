<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: isdst

namespace Dogma\Time\Repetition\Rule;

use Dogma\Check;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use Dogma\Time\TimeZone;

class SummerTimeRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    public const SUMMER_TIME_START = 'start';
    public const SUMMER_TIME_END = 'end';
    public const SUMMER_TIME_CHANGE = 'change';

    /** @var string */
    private $type;

    /** @var \DateTimeZone */
    private $timeZone;

    public function __construct(string $type, TimeZone $timeZone)
    {
        Check::enum($type, self::SUMMER_TIME_START, self::SUMMER_TIME_END);

        $this->type = $type;
        $this->timeZone = $timeZone->getDateTimeZone();
    }

    public function getNext(DateTime $after): ?DateTime
    {
        $since = $after->getTimestamp();
        $transitions = $this->timeZone->getTransitions($since);
        foreach ($transitions as $transition) {
            if ($this->type === self::SUMMER_TIME_CHANGE) {
                return DateTime::createFromTimestamp($transition['ts'], $this->timeZone);
            } elseif ($this->type === self::SUMMER_TIME_START && $transition['isdst']) {
                return DateTime::createFromTimestamp($transition['ts'], $this->timeZone);
            } elseif ($this->type === self::SUMMER_TIME_END && !$transition['isdst']) {
                return DateTime::createFromTimestamp($transition['ts'], $this->timeZone);
            }
        }

        return null;
    }

}
