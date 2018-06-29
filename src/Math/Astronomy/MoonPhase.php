<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Astronomy;

use Dogma\Enum\StringEnum;

class MoonPhase extends StringEnum
{

    public const NEW_MOON = 'new-moon';
    public const WAXING_CRESCENT = 'waxing-crescent';
    public const FIRST_QUARTER = 'first-quarter';
    public const WAXING_GIBBOUS = 'waxing-gibbous';
    public const FULL_MOON = 'full-moon';
    public const WANING_GIBBOUS = 'waning-gibbous';
    public const THIRD_QUARTER = 'third-quarter';
    public const WANING_CRESCENT = 'waning-crescent';

    /** @var string[] */
    private static $names = [
        self::NEW_MOON => 'New Moon',
        self::WAXING_CRESCENT => 'Waxing Crescent',
        self::FIRST_QUARTER => 'First Quarter',
        self::WAXING_GIBBOUS => 'Waxing Gibbous',
        self::FULL_MOON => 'Full Moon',
        self::WANING_GIBBOUS => 'Waning Gibbous',
        self::THIRD_QUARTER => 'Third Quarter',
        self::WANING_CRESCENT => 'Waning Crescent',
    ];

    /** @var float[] */
    private static $phases = [
        self::NEW_MOON => 0.0,
        self::WAXING_CRESCENT => 0.125,
        self::FIRST_QUARTER => 0.25,
        self::WAXING_GIBBOUS => 0.375,
        self::FULL_MOON => 0.5,
        self::WANING_GIBBOUS => 0.625,
        self::THIRD_QUARTER => 0.75,
        self::WANING_CRESCENT => 0.825,
    ];

    public function getName(): string
    {
        return self::$names[$this->getValue()];
    }

    public function getPhase(): float
    {
        return self::$phases[$this->getValue()];
    }

}
