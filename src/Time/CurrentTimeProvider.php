<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time;

class CurrentTimeProvider implements \Dogma\Time\TimeProvider
{
    use \Dogma\StrictBehaviorMixin;

    /** @var \DateTimeZone|null */
    private $timeZone;

    public function __construct(?\DateTimeZone $timeZone = null)
    {
        $this->timeZone = $timeZone;
    }

    public function getDate(): Date
    {
        return $this->getDateTime()->getDate();
    }

    public function getDateTime(): DateTime
    {
        $currentTime = new DateTime();

        if ($this->timeZone !== null) {
            return $currentTime->setTimezone($this->timeZone);
        }

        return $currentTime;
    }

    public function getTimeZone(): \DateTimeZone
    {
        return $this->timeZone ?? $this->getDateTime()->getTimezone();
    }

}
