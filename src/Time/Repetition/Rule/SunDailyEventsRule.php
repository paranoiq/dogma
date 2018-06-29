<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Repetition\Rule;

use Dogma\Check;
use Dogma\Geolocation\Position;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;
use Dogma\Time\Seconds;

class SunDailyEventsRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    public const SUNRISE = 'sunrise';
    public const SUNSET = 'sunset';

    /** @var string[] */
    private static $events = [
        self::SUNRISE,
        self::SUNSET,
    ];

    /** @var string */
    private $event;

    /** @var \Dogma\Geolocation\Position */
    private $position;

    public function __construct(string $event, Position $position)
    {
        Check::enum($event, self::$events);

        $this->event = $event;
        $this->position = $position;
    }

    public function getNext(DateTime $after): DateTime
    {
        switch ($this->event) {
            case self::SUNRISE:
                return $this->getNextSunrise($after);
            case self::SUNSET:
                return $this->getNextSunset($after);
            default:
                throw new ShouldNotHappenException('Undefined event.');
        }
    }

    public function getNextSunrise(DateTime $after): DateTime
    {
        $afterTimestamp = $after->getTimestamp();

        $timestamp = date_sunrise($afterTimestamp, SUNFUNCS_RET_TIMESTAMP, $this->position->getLatitude(), $this->position->getLongitude());
        $dateTime = DateTime::createFromTimestamp($timestamp, $after->getTimezone());
        if ($dateTime->isAfter($after)) {
            return $dateTime;
        }

        $afterTimestamp = $afterTimestamp + Seconds::DAY;
        $timestamp = date_sunrise($afterTimestamp, SUNFUNCS_RET_TIMESTAMP, $this->position->getLatitude(), $this->position->getLongitude());

        return DateTime::createFromTimestamp($timestamp, $after->getTimezone());
    }

    public function getNextSunset(DateTime $after): DateTime
    {
        $afterTimestamp = $after->getTimestamp();

        $timestamp = date_sunset($afterTimestamp, SUNFUNCS_RET_TIMESTAMP, $this->position->getLatitude(), $this->position->getLongitude());
        $dateTime = DateTime::createFromTimestamp($timestamp, $after->getTimezone());
        if ($dateTime->isAfter($after)) {
            return $dateTime;
        }

        $afterTimestamp = $afterTimestamp + Seconds::DAY;
        $timestamp = date_sunset($afterTimestamp, SUNFUNCS_RET_TIMESTAMP, $this->position->getLatitude(), $this->position->getLongitude());

        return DateTime::createFromTimestamp($timestamp, $after->getTimezone());
    }

}
