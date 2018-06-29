<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: stackoverflow

namespace Dogma\Time\Repetition\Rule;

use Dogma\Check;
use Dogma\Geolocation\Position;
use Dogma\ShouldNotHappenException;
use Dogma\StrictBehaviorMixin;
use Dogma\Time\DateTime;

class SunYearlyEventsRule implements RepetitionRule
{
    use StrictBehaviorMixin;

    public const WINTER_SOLSTICE = 'winter-solstice';
    public const SPRING_EQUINOX = 'spring-equinox';
    public const SUMMER_SOLSTICE = 'summer-solstice';
    public const AUTUMN_EQUINOX = 'autumn-equinox';

    /** @var string[] */
    private static $events = [
        self::WINTER_SOLSTICE,
        self::SPRING_EQUINOX,
        self::SUMMER_SOLSTICE,
        self::AUTUMN_EQUINOX,
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
            case self::WINTER_SOLSTICE:
                return $this->getNextWinterSolstice($after);
            case self::SPRING_EQUINOX:
                return $this->getNextSpringEquinox($after);
            case self::SUMMER_SOLSTICE:
                return $this->getNextSummerSolstice($after);
            case self::AUTUMN_EQUINOX:
                return $this->getNextAutumnEquinox($after);
            default:
                throw new ShouldNotHappenException('Undefined event.');
        }
    }

    public function getNextWinterSolstice(DateTime $after): DateTime
    {
        /// https://stackoverflow.com/questions/23978449/calculating-summer-winter-solstice-in-php
    }

    public function getNextSpringEquinox(DateTime $after): DateTime
    {
        ///
    }

    public function getNextSummerSolstice(DateTime $after): DateTime
    {
        ///
    }

    public function getNextAutumnEquinox(DateTime $after): DateTime
    {
        ///
    }

}
