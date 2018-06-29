<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

/**
 * Moon phase calculation class
 * Adapted for PHP from Moontool for Windows (http://www.fourmilab.ch/moontoolw/) by Samir Shah (http://rayofsolaris.net)
 * License: MIT
 */

// spell-check-ignore: Moontool fourmilab moontoolw Samir rayofsolaris

namespace Dogma\Math\Astronomy;

use Dogma\StrictBehaviorMixin;

class MoonState
{
    use StrictBehaviorMixin;

    // 1980 January 0.0
    private const EPOCH = 2444238.5;

    /* Constants defining the Sun's apparent orbit */
    // Ecliptic longitude of the Sun at epoch 1980.0
    private const SUN_LONGITUDE_EPOCH = 278.833540;
    // Ecliptic longitude of the Sun at perigee
    private const SUN_LONGITUDE_PERIGEE = 282.596403;
    // Eccentricity of Earth's orbit
    private const EARTH_ECCENTRICITY = 0.016718;
    // Semi-major axis of Earth's orbit, km
    private const SUN_SEMI_MAJOR_AXIS = 1.495985e8;
    // Sun's angular size, degrees, at semi-major axis distance
    private const SUN_ANGULAR_SIZE = 0.533128;

    /* Elements of the Moon's orbit, epoch 1980.0 */
    // Moon's mean longitude at the epoch
    private const MOON_MEAN_LONGITUDE_EPOCH = 64.975464;
    // Mean longitude of the perigee at the epoch
    private const MOON_PERIGEE_LONGITUDE_EPOCH = 349.383063;
    // Mean longitude of the node at the epoch
    protected const MOON_NODE_LONGITUDE_EPOCH = 151.950429;
    // Inclination of the Moon's orbit
    protected const MOON_INCLINATION = 5.145396;
    // Eccentricity of the Moon's orbit
    private const MOON_ECCENTRICITY = 0.054900;
    // Moon's angular size at distance a from Earth
    private const MOON_ANGULAR_SIZE = 0.5181;
    // Semi-major axis of Moon's orbit in km
    private const MOON_SEMI_MAJOR_AXIS = 384401;
    // Parallax at distance a from Earth
    protected const MOON_PARALLAX = 0.9507;

    // Synodic month (new Moon to new Moon)
    public const SYNODIC_MONTH = 29.53058868;

    // Base date for E. W. Brown's numbered series of lunations (1923 January 16)
    protected const LUNATIONS_BASE = 2423436.0;

    // Radius of Earth in kilometres (on equator)
    protected const EARTH_RADIUS = 6378.16;

    /** @var int */
    private $timestamp;

    /** @var float */
    private $moonPhase;

    /** @var float */
    private $moonIllumination;

    /** @var float */
    private $moonAge;

    /** @var float */
    private $moonDistance;

    /** @var float */
    private $moonAngularSize;

    /** @var float */
    private $sunDistance;

    /** @var float */
    private $sunAngularSize;

    public function __construct(int $timestamp)
    {
        $this->timestamp = $timestamp;

        $julianDay = JulianDayConverter::timestampToJulianDay($timestamp);

        /* Calculation of the Sun's position */
        // Date within epoch
        $day = $julianDay - self::EPOCH;
        // Mean anomaly of the Sun
        $sunMeanAnomaly = $this->fixAngle((360 / 365.2422) * $day);
        // Convert from perigee coordinates to epoch 1980.0
        $sunMeanAnomalyEpoch = $this->fixAngle($sunMeanAnomaly + self::SUN_LONGITUDE_EPOCH - self::SUN_LONGITUDE_PERIGEE);
        // Solve equation of Kepler
        $eccentricity = $this->keplerEquation($sunMeanAnomalyEpoch, self::EARTH_ECCENTRICITY);
        $eccentricity = sqrt((1 + self::EARTH_ECCENTRICITY) / (1 - self::EARTH_ECCENTRICITY)) * tan($eccentricity / 2);
        // True anomaly
        $eccentricity = 2 * rad2deg(atan($eccentricity));
        // Sun's geocentric ecliptic longitude
        $sunLambda = $this->fixAngle($eccentricity + self::SUN_LONGITUDE_PERIGEE);
        // Orbital distance factor
        $distanceFactor = ((1 + self::EARTH_ECCENTRICITY * cos(deg2rad($eccentricity))) / (1 - self::EARTH_ECCENTRICITY * self::EARTH_ECCENTRICITY));
        // Distance to Sun in km
        $sunDistance = self::SUN_SEMI_MAJOR_AXIS / $distanceFactor;
        // Sun's angular size in degrees
        $sunAngularSize = self::SUN_ANGULAR_SIZE * $distanceFactor;

        /* Calculation of the Moon's position */
        // Moon's mean longitude
        $moonLongitude = $this->fixAngle(13.1763966 * $day + self::MOON_MEAN_LONGITUDE_EPOCH);
        // Moon's mean anomaly
        $moonMeanAnomaly = $this->fixAngle($moonLongitude - 0.1114041 * $day - self::MOON_PERIGEE_LONGITUDE_EPOCH);
        // Moon's ascending node mean longitude
        //$nodeMeanLongitude = $this->fixAngle(self::MOON_NODE_LONGITUDE_EPOCH - 0.0529539 * $day);
        // Evection
        $evection = 1.2739 * sin(deg2rad(2 * ($moonLongitude - $sunLambda) - $moonMeanAnomaly));
        // Annual equation
        $annualEquation = 0.1858 * sin(deg2rad($sunMeanAnomalyEpoch));
        // Correction term
        $a3 = 0.37 * sin(deg2rad($sunMeanAnomalyEpoch));
        // Corrected anomaly
        $mmP = $moonMeanAnomaly + $evection - $annualEquation - $a3;
        // Correction for the equation of the centre
        $mEc = 6.2886 * sin(deg2rad($mmP));
        // Another correction term
        $a4 = 0.214 * sin(deg2rad(2 * $mmP));
        // Corrected longitude
        $lP = $moonLongitude + $evection + $mEc - $annualEquation + $a4;
        // Variation
        $variation = 0.6583 * sin(deg2rad(2 * ($lP - $sunLambda)));
        // True longitude
        $longitude = $lP + $variation;
        // Corrected longitude of the node
        //$nodeLongitude = $nodeMeanLongitude - 0.16 * sin(deg2rad($sunMeanAnomalyEpoch));
        // Y inclination coordinate
        //$y = sin(deg2rad($longitude - $nodeLongitude)) * cos(deg2rad(self::MOON_INCLINATION));
        // X inclination coordinate
        //$x = cos(deg2rad($longitude - $nodeLongitude));
        // Ecliptic longitude
        //$moonLambda = rad2deg(atan2($y, $x)) + $nodeLongitude;
        // Ecliptic latitude
        //$moonBeta = rad2deg(asin(sin(deg2rad($longitude - $nodeLongitude)) * sin(deg2rad(self::MOON_INCLINATION))));

        /* Calculation of the phase of the Moon */
        // Age of the Moon in degrees
        $moonAge = $longitude - $sunLambda;
        // Phase of the Moon
        $moonPhase = (1 - cos(deg2rad($moonAge))) / 2;

        // Distance of moon from the centre of the Earth
        $moonDistance = (self::MOON_SEMI_MAJOR_AXIS * (1 - self::MOON_ECCENTRICITY ** 2)) / (1 + self::MOON_ECCENTRICITY * cos(deg2rad($mmP + $mEc)));
        $moonDistanceFraction = $moonDistance / self::MOON_SEMI_MAJOR_AXIS;
        // Moon's angular diameter
        $moonAngularSize = self::MOON_ANGULAR_SIZE / $moonDistanceFraction;
        // Moon's parallax
        //$moonParallax = self::MOON_PARALLAX / $moonDistanceFraction;

        // Phase (0 to 1)
        $this->moonPhase = $this->fixAngle($moonAge) / 360;
        // Illuminated fraction (0 to 1)
        $this->moonIllumination = $moonPhase;
        // Age of moon (days)
        $this->moonAge = self::SYNODIC_MONTH * $this->moonPhase;
        // Distance (kilometres)
        $this->moonDistance = $moonDistance;
        // Angular diameter (degrees)
        $this->moonAngularSize = $moonAngularSize;
        // Distance to Sun (kilometres)
        $this->sunDistance = $sunDistance;
        // Sun's angular diameter (degrees)
        $this->sunAngularSize = $sunAngularSize;
    }

    private function fixAngle(float $angle): float
    {
        return $angle - 360 * floor($angle / 360);
    }

    private function keplerEquation(float $meanAnomaly, float $eccentricAnomaly): float
    {
        $epsilon = 0.000001;
        $eccentricity = $meanAnomaly = deg2rad($meanAnomaly);
        do {
            $delta = $eccentricity - $eccentricAnomaly * sin($eccentricity) - $meanAnomaly;
            $eccentricity -= $delta / (1 - $eccentricAnomaly * cos($eccentricity));
        } while (abs($delta) > $epsilon);

        return $eccentricity;
    }

    // getters ---------------------------------------------------------------------------------------------------------

    /**
     * Phase (0 to 1)
     * @return float
     */
    public function phase(): float
    {
        return $this->moonPhase;
    }

    /**
     * Illuminated fraction (0 to 1)
     * @return float
     */
    public function illumination(): float
    {
        return $this->moonIllumination;
    }

    /**
     * Age of moon (days)
     * @return float
     */
    public function age(): float
    {
        return $this->moonAge;
    }

    /**
     * Distance (kilometres)
     * @return float
     */
    public function distance(): float
    {
        return $this->moonDistance;
    }

    /**
     * Angular diameter (degrees)
     * @return float
     */
    public function diameter(): float
    {
        return $this->moonAngularSize;
    }

    /**
     * Distance to Sun (kilometres)
     * @return float
     */
    public function sunDistance(): float
    {
        return $this->sunDistance;
    }

    /**
     * Sun's angular diameter (degrees)
     * @return float
     */
    public function sunDiameter(): float
    {
        return $this->sunAngularSize;
    }

    public function getMoonPhase(): MoonPhase
    {
        static $phases = [
            MoonPhase::NEW_MOON,
            MoonPhase::WANING_CRESCENT,
            MoonPhase::FIRST_QUARTER,
            MoonPhase::WANING_GIBBOUS,
            MoonPhase::FULL_MOON,
            MoonPhase::WANING_GIBBOUS,
            MoonPhase::THIRD_QUARTER,
            MoonPhase::WANING_CRESCENT,
            MoonPhase::NEW_MOON,
        ];

        // There are eight phases, evenly split. A "New Moon" occupies the 1/16th phases either side of phase = 0, and the rest follow from that.
        $phase = $phases[(int) floor(($this->moonPhase + 0.0625) * 8)];

        return MoonPhase::get($phase);
    }

}
