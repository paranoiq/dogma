<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Geolocation;

use Dogma\Math\Angle\AngleFormatter;

class PositionFormatter
{
    use \Dogma\StaticClassMixin;

    public const LATITUDE = 'l';
    public const LATITUDE_SIGNED = 'L';
    public const LATITUDE_NORTH_SOUTH = 'N';
    public const LATITUDE_NS = 'n';
    public const LONGITUDE = 'o';
    public const LONGITUDE_SIGNED = 'O';
    public const LONGITUDE_EAST_WEST = 'E';
    public const LONGITUDE_EW = 'e';

    // reserved
    private const ALTITUDE = 'a';
    private const ALTITUDE_SIGNED = 'A';

    public const FORMAT_PRETTY = 'nl,eo';
    public const FORMAT_DEFAULT = 'L,O';

    /** @var string[] */
    private static $specialCharacters = [
        self::LATITUDE,
        self::LATITUDE_SIGNED,
        self::LATITUDE_NORTH_SOUTH,
        self::LATITUDE_NS,
        self::LONGITUDE,
        self::LONGITUDE_SIGNED,
        self::LONGITUDE_EAST_WEST,
        self::LONGITUDE_EW,
        self::ALTITUDE,
        self::ALTITUDE_SIGNED,
    ];

    /** @var \Dogma\Math\Angle\AngleFormatter */
    private $angleFormatter;

    /** @var string */
    private $format;

    public function __construct(string $format = self::FORMAT_DEFAULT, ?AngleFormatter $angleFormatter = null)
    {
        $this->angleFormatter = $angleFormatter ?? new AngleFormatter(AngleFormatter::FORMAT_NUMBER);
        $this->format = $format;
    }

    public function format(
        Position $position,
        string $format = self::FORMAT_DEFAULT,
        ?string $angleFormatter = null
    ): string
    {
        $angleFormatter = $angleFormatter ?? $this->angleFormatter;

        $result = '';
        $escaped = false;
        foreach (str_split($format) as $character) {
            if ($character === '%' && !$escaped) {
                $escaped = true;
            } elseif ($escaped === false && in_array($character, self::$specialCharacters)) {
                switch ($character) {
                    case self::LATITUDE:
                        $result .= $angleFormatter->format(abs($position->getLatitude()));
                        break;
                    case self::LATITUDE_SIGNED:
                        $result .= $angleFormatter->format($position->getLatitude());
                        break;
                    case self::LATITUDE_NORTH_SOUTH:
                        $result .= $position->getLatitude() < 0.0 ? 'south' : 'north';
                        break;
                    case self::LATITUDE_NS:
                        $result .= $position->getLatitude() < 0.0 ? 'S' : 'N';
                        break;
                    case self::LONGITUDE:
                        $result .= $angleFormatter->format(abs($position->getLongitude()));
                        break;
                    case self::LONGITUDE_SIGNED:
                        $result .= $angleFormatter->format($position->getLongitude());
                        break;
                    case self::LONGITUDE_EAST_WEST:
                        $result .= $position->getLongitude() < 0.0 ? 'west' : 'east';
                        break;
                    case self::LONGITUDE_EW:
                        $result .= $position->getLongitude() < 0.0 ? 'W' : 'E';
                        break;
                    case self::ALTITUDE:
                        $result .= '0';
                        break;
                    case self::ALTITUDE_SIGNED:
                        $result .= '0';
                        break;
                }
                $escaped = false;
            } else {
                $result .= $character;
            }
        }

        return $result;
    }

}
