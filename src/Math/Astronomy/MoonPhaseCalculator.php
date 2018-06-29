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
use Dogma\Time\DateTime;
use Dogma\Time\Seconds;

class MoonPhaseCalculator
{
    use StrictBehaviorMixin;

    public function getPreviousDateTime(MoonPhase $moonPhase, DateTime $dateTime): DateTime
    {
        $timestamp = self::getPreviousTimestamp($moonPhase, $dateTime->getTimestamp());

        return DateTime::createFromTimestamp($timestamp, $dateTime->getTimezone());
    }

    public function getPreviousTimestamp(MoonPhase $moonPhase, int $timestamp): int
    {
        $julianDay = $this->findPhase($moonPhase, JulianDayConverter::timestampToJulianDay($timestamp), $timestamp, false);

        return JulianDayConverter::julianDayToTimestamp($julianDay);
    }

    public function getPreviousJulianDay(MoonPhase $moonPhase, float $julianDay): float
    {
        return $this->findPhase($moonPhase, $julianDay, JulianDayConverter::julianDayToTimestamp($julianDay), false);
    }

    public function getNextDateTime(MoonPhase $moonPhase, DateTime $dateTime): DateTime
    {
        $timestamp = self::getNextTimestamp($moonPhase, $dateTime->getTimestamp());

        return DateTime::createFromTimestamp($timestamp, $dateTime->getTimezone());
    }

    public function getNextTimestamp(MoonPhase $moonPhase, int $timestamp): int
    {
        $julianDay = $this->findPhase($moonPhase, JulianDayConverter::timestampToJulianDay($timestamp), $timestamp, true);

        return JulianDayConverter::julianDayToTimestamp($julianDay);
    }

    public function getNextJulianDay(MoonPhase $moonPhase, float $julianDay): float
    {
        return $this->findPhase($moonPhase, $julianDay, JulianDayConverter::julianDayToTimestamp($julianDay), true);
    }

    /**
     * Find time of phases of the moon which surround the current date.
     * Five phases are found, starting and ending with the new moons which bound the current lunation.
     * @param \Dogma\Math\Astronomy\MoonPhase $moonPhase
     * @param float $julianDay
     * @param int $timestamp
     * @param bool $next
     * @return float
     */
    private function findPhase(MoonPhase $moonPhase, float $julianDay, int $timestamp, bool $next = true): float
    {
        $aDate = $julianDay - 45;
        $aTimestamp = $timestamp - Seconds::DAY * 45;
        $year = (int) gmdate('Y', $aTimestamp);
        $month = (int) gmdate('n', $aTimestamp);
        $k1 = floor(($year + (($month - 1) * (1 / 12)) - 1900) * 12.3685);
        $k2 = $k1 + 1;
        $aDate = $nt1 = $this->meanPhase($aDate, $k1);

        while (true) {
            $aDate += MoonState::SYNODIC_MONTH;
            $k2 = $k1 + 1;
            $nt2 = $this->meanPhase($aDate, $k2);
            // if nt2 is close to day, then mean phase isn't good enough, we have to be more accurate
            if (abs($nt2 - $julianDay) < 0.75) {
                $nt2 = $this->truePhase($k2, 0.0);
            }
            if ($nt1 <= $julianDay && $nt2 > $julianDay) {
                break;
            }
            $nt1 = $nt2;
            $k1 = $k2;
        }

        if ($next) {
            return $this->truePhase($k2, $moonPhase->getPhase());
        } else {
            return $this->truePhase($k1, $moonPhase->getPhase());
        }
    }

    /**
     * Calculates time of the mean new Moon for a given base date. This argument K to this function is the
     * precomputed synodic month index, given by: K = (year - 1900) * 12.3685
     * where year is expressed as a year and fractional year.
     * @param float $day
     * @param float $k
     * @return float
     */
    private function meanPhase(float $day, float $k): float
    {
        // Time in Julian centuries from 1900 January 0.5
        $t = ($day - 2415020.0) / 36525;
        $t2 = $t * $t;
        $t3 = $t2 * $t;
        $nt1 = 2415020.75933 + MoonState::SYNODIC_MONTH * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * sin(deg2rad(166.56 + 132.87 * $t - 0.009173 * $t2));

        return $nt1;
    }

    /**
     * Given a K value used to determine the mean phase of the new moon, and a phase selector (0.0, 0.25, 0.5, 0.75),
     * obtain the true, corrected phase time.
     * @param float $k
     * @param float $phase
     * @return float|null
     */
    private function truePhase(float $k, float $phase): ?float
    {
        // Add phase to new moon time
        $k += $phase;
        // Time in Julian centuries from 1900 January 0.5
        $t = $k / 1236.85;
        // Square for frequent use
        $t2 = $t * $t;
        // Cube for frequent use
        $t3 = $t2 * $t;
        // Mean time of phase
        $phaseTime = 2415020.75933
            + MoonState::SYNODIC_MONTH * $k
            + 0.0001178 * $t2
            - 0.000000155 * $t3
            + 0.00033 * sin(deg2rad(166.56 + 132.87 * $t - 0.009173 * $t2));
        // Sun's mean anomaly
        $sunMeanAnomaly = 359.2242 + 29.10535608 * $k - 0.0000333 * $t2 - 0.00000347 * $t3;
        // Moon's mean anomaly
        $moonMeanAnomaly = 306.0253 + 385.81691806 * $k + 0.0107306 * $t2 + 0.00001236 * $t3;
        // Moon's argument of latitude
        $f = 21.2964 + 390.67050646 * $k - 0.0016528 * $t2 - 0.00000239 * $t3;

        if ($phase < 0.01 || abs($phase - 0.5) < 0.01) {
            // Corrections for New and Full Moon
            $phaseTime += (0.1734 - 0.000393 * $t) * sin(deg2rad($sunMeanAnomaly))
                + 0.0021 * sin(deg2rad(2 * $sunMeanAnomaly))
                - 0.4068 * sin(deg2rad($moonMeanAnomaly))
                + 0.0161 * sin(deg2rad(2 * $moonMeanAnomaly))
                - 0.0004 * sin(deg2rad(3 * $moonMeanAnomaly))
                + 0.0104 * sin(deg2rad(2 * $f))
                - 0.0051 * sin(deg2rad($sunMeanAnomaly + $moonMeanAnomaly))
                - 0.0074 * sin(deg2rad($sunMeanAnomaly - $moonMeanAnomaly))
                + 0.0004 * sin(deg2rad(2 * $f + $sunMeanAnomaly))
                - 0.0004 * sin(deg2rad(2 * $f - $sunMeanAnomaly))
                - 0.0006 * sin(deg2rad(2 * $f + $moonMeanAnomaly))
                + 0.0010 * sin(deg2rad(2 * $f - $moonMeanAnomaly))
                + 0.0005 * sin(deg2rad($sunMeanAnomaly + 2 * $moonMeanAnomaly));

            return $phaseTime;

        } elseif (abs($phase - 0.25) < 0.01 || abs($phase - 0.75) < 0.01) {
            $phaseTime += (0.1721 - 0.0004 * $t) * sin(deg2rad($sunMeanAnomaly))
                + 0.0021 * sin(deg2rad(2 * $sunMeanAnomaly))
                - 0.6280 * sin(deg2rad($moonMeanAnomaly))
                + 0.0089 * sin(deg2rad(2 * $moonMeanAnomaly))
                - 0.0004 * sin(deg2rad(3 * $moonMeanAnomaly))
                + 0.0079 * sin(deg2rad(2 * $f))
                - 0.0119 * sin(deg2rad($sunMeanAnomaly + $moonMeanAnomaly))
                - 0.0047 * sin(deg2rad($sunMeanAnomaly - $moonMeanAnomaly))
                + 0.0003 * sin(deg2rad(2 * $f + $sunMeanAnomaly))
                - 0.0004 * sin(deg2rad(2 * $f - $sunMeanAnomaly))
                - 0.0006 * sin(deg2rad(2 * $f + $moonMeanAnomaly))
                + 0.0021 * sin(deg2rad(2 * $f - $moonMeanAnomaly))
                + 0.0003 * sin(deg2rad($sunMeanAnomaly + 2 * $moonMeanAnomaly))
                + 0.0004 * sin(deg2rad($sunMeanAnomaly - 2 * $moonMeanAnomaly))
                - 0.0003 * sin(deg2rad(2 * $sunMeanAnomaly + $moonMeanAnomaly));

            if ($phase < 0.5) {
                // First quarter correction
                $phaseTime += 0.0028 - 0.0004 * cos(deg2rad($sunMeanAnomaly)) + 0.0003 * cos(deg2rad($moonMeanAnomaly));
            } else {
                // Last quarter correction
                $phaseTime += -0.0028 + 0.0004 * cos(deg2rad($sunMeanAnomaly)) - 0.0003 * cos(deg2rad($moonMeanAnomaly));
            }

            return $phaseTime;

        } else {
            // function was called with an invalid phase selector
            return null;
        }
    }

}
