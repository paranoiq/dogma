<?php

namespace Dogma\Tests\Time\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DateTime;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Interval\WeekInterval;
use Dogma\Time\InvalidWeekIntervalException;

require_once __DIR__ . '/../../bootstrap.php';

$startDate = new DateTime('2000-01-10 00:00:00.000000');
$endDate = new DateTime('2000-01-17 00:00:00.000000');
$interval = new WeekInterval($startDate, $endDate);

$dt = function (int $day) {
    return new DateTime('2000-01-' . $day . ' 00:00:00');
};

// wrong start day
Assert::exception(function () use ($dt) {
    new WeekInterval($dt(1), $dt(8));
}, InvalidWeekIntervalException::class);

// wrong start time
Assert::exception(function () {
    new WeekInterval(new DateTime('2000-01-10 01:00:00'), new DateTime('2000-01-17 01:00:00'));
}, InvalidWeekIntervalException::class);

// too short
Assert::exception(function () use ($startDate, $dt) {
    new WeekInterval($startDate, $dt(16));
}, InvalidWeekIntervalException::class);

// too long
Assert::exception(function () use ($startDate, $dt) {
    new WeekInterval($startDate, $dt(20));
}, InvalidWeekIntervalException::class);

// createFromDate()
Assert::equal(WeekInterval::createFromDate(new Date('2000-01-13')), $interval);

// createFromDateTime()
Assert::equal(WeekInterval::createFromDateTime($dt(13)), $interval);

// createFromYearAndWeekNumber()
Assert::equal(WeekInterval::createFromIsoYearAndWeek(2000, 2), $interval);

// previous()
$previous = WeekInterval::createFromIsoYearAndWeek(2000, 1);
Assert::equal($interval->previous(), $previous);

// next()
$next = WeekInterval::createFromIsoYearAndWeek(2000, 3);
Assert::equal($interval->next(), $next);

// createOverlappingIntervals()
Assert::equal(WeekInterval::createOverlappingIntervals($interval), [$interval]);
Assert::equal(WeekInterval::createOverlappingIntervals(new DateTimeInterval($dt(5), $dt(20))), [$previous, $interval, $next]);
