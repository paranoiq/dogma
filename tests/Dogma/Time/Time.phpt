<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use Dogma\Time\Time;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Tester\Assert;
use Dogma\ValueOutOfRangeException;

require_once __DIR__ . '/../bootstrap.php';

$timeString = '03:04:05';
$time = new Time($timeString);
$seconds = 11045;

// __construct()
Assert::throws(function () {
    new Time(-200);
}, ValueOutOfRangeException::class);

// createFromParts()
Assert::throws(function () {
    Time::createFromParts(-1, 0, 0);
}, ValueOutOfRangeException::class);
Assert::type(Time::createFromParts(3, 4, 5), Time::class);
Assert::same(Time::createFromParts(3, 4, 5)->format(), $timeString);

// createFromString()
Assert::throws(function () {
    new Time('asdf');
}, InvalidDateTimeException::class);
Assert::same((new Time($timeString))->format(), $timeString);

// createFromSeconds()
Assert::throws(function () {
    Time::createFromSeconds(-1);
}, ValueOutOfRangeException::class);
Assert::type(Time::createFromSeconds($seconds), Time::class);
Assert::same(Time::createFromSeconds($seconds)->format(), $timeString);

// createFromFormat()
Assert::throws(function () {
    Time::createFromFormat(Time::DEFAULT_FORMAT, 'asdf');
}, InvalidDateTimeException::class);
Assert::type(Time::createFromFormat(Time::DEFAULT_FORMAT, $timeString), Time::class);
Assert::same(Time::createFromFormat(Time::DEFAULT_FORMAT, $timeString)->format(), $timeString);

// getMicroTime()
Assert::same($time->getMicroTime(), $seconds * 1000000);

// getHours()
Assert::same($time->getHours(), 3);

// getMinutes()
Assert::same($time->getMinutes(), 4);

// getSeconds()
Assert::same($time->getSeconds(), 5);

// isEquals()
Assert::false($time->equals(new Time(0)));
Assert::true($time->equals(new Time($timeString)));

// isBetween()
Assert::false($time->isBetween('10:00:00', '12:00:00'));
Assert::true($time->isBetween('02:00:00', '04:00:00'));
Assert::true($time->isBetween('22:00:00', '04:00:00'));
