<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use Dogma\Time\Time;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Tester\Assert;
use Dogma\ValueOutOfRangeException;

require_once __DIR__ . '/../bootstrap.php';

$timeString = '03:04:05.000006';
$time = new Time($timeString);
$microSeconds = 11045000006;
$denormalizedTime = new Time('27:04:05.000006');
$denormalizedMicroSeconds = 97445000006;

// __construct()
Assert::throws(function () {
    new Time(-200);
}, ValueOutOfRangeException::class);
Assert::throws(function () {
    new Time('asdf');
}, InvalidDateTimeException::class);

Assert::same((new Time($timeString))->format(), $timeString);

// createFromParts()
Assert::throws(function () {
    Time::createFromParts(-1, 0, 0);
}, ValueOutOfRangeException::class);
Assert::type(Time::createFromParts(3, 4, 5, 6), Time::class);
Assert::same(Time::createFromParts(3, 4, 5, 6)->format(), $timeString);

// createFromSeconds()
Assert::throws(function () {
    Time::createFromSeconds(-1);
}, ValueOutOfRangeException::class);
Assert::type(Time::createFromSeconds((int) ($microSeconds / 1000000)), Time::class);
Assert::same(Time::createFromSeconds((int) ($microSeconds / 1000000))->format(), '03:04:05.000000');

// createFromFormat()
Assert::throws(function () {
    Time::createFromFormat(Time::DEFAULT_FORMAT, 'asdf');
}, InvalidDateTimeException::class);
Assert::type(Time::createFromFormat(Time::DEFAULT_FORMAT, $timeString), Time::class);
Assert::same(Time::createFromFormat(Time::DEFAULT_FORMAT, $timeString)->format(), $timeString);

// normalize()
Assert::same($time->normalize()->getMicroTime(), $microSeconds);
Assert::same($denormalizedTime->normalize()->getMicroTime(), $microSeconds);

// denormalize()
Assert::same($time->denormalize()->getMicroTime(), $denormalizedMicroSeconds);
Assert::same($denormalizedTime->denormalize()->getMicroTime(), $denormalizedMicroSeconds);

// modify
Assert::same($time->modify('+1 hour')->getMicroTime(), $microSeconds + 3600 * 1000000);
Assert::same($denormalizedTime->modify('+1 hour')->getMicroTime(), $denormalizedMicroSeconds + 3600 * 1000000);
/// overflows

// getMicroTime()
Assert::same($time->getMicroTime(), $microSeconds);
Assert::same($denormalizedTime->getMicroTime(), $denormalizedMicroSeconds);

// getHours()
Assert::same($time->getHours(), 3);
Assert::same($denormalizedTime->getHours(), 3);

// getMinutes()
Assert::same($time->getMinutes(), 4);
Assert::same($denormalizedTime->getMinutes(), 4);

// getSeconds()
Assert::same($time->getSeconds(), 5);
Assert::same($denormalizedTime->getSeconds(), 5);

// getMicroseconds()
Assert::same($time->getMicroseconds(), 6);
Assert::same($denormalizedTime->getMicroseconds(), 6);

$before = new Time('02:00:00');
$after = new Time('04:00:00');

// isEquals()
Assert::false($time->equals(new Time(0)));
Assert::true($time->equals(new Time($timeString)));

// compare()
Assert::same($time->compare($before), 1);
Assert::same($time->compare($time), 0);
Assert::same($time->compare($after), -1);

// isBefore()
Assert::false($time->isBefore($before));
Assert::false($time->isBefore($time));
Assert::true($time->isBefore($after));

// isAfter()
Assert::true($time->isAfter($before));
Assert::false($time->isAfter($time));
Assert::false($time->isAfter($after));

// isSameOrBefore()
Assert::false($time->isSameOrBefore($before));
Assert::true($time->isSameOrBefore($time));
Assert::true($time->isSameOrBefore($after));

// isSameOrAfter()
Assert::true($time->isSameOrAfter($before));
Assert::true($time->isSameOrAfter($time));
Assert::false($time->isSameOrAfter($after));

// isBetween()
Assert::false($time->isBetween(new Time('10:00:00'), new Time('12:00:00')));
Assert::true($time->isBetween(new Time('02:00:00'), new Time('04:00:00')));
Assert::true($time->isBetween(new Time('22:00:00'), new TIme('04:00:00')));
