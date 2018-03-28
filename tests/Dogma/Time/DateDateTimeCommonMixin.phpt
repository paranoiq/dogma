<?php declare(strict_types = 1);

namespace Dogma\Tests\Time;

use Dogma\InvalidValueException;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\DayOfWeek;
use Dogma\Time\Month;

require_once __DIR__ . '/../bootstrap.php';

$monday = new Date('2016-11-07');
$friday = new Date('2016-11-04');
$saturday = new Date('2016-11-05');
$sunday = new Date('2016-11-06');

// getDayOfWeekEnum()
Assert::same($monday->getDayOfWeekEnum(), DayOfWeek::get(DayOfWeek::MONDAY));
Assert::same($friday->getDayOfWeekEnum(), DayOfWeek::get(DayOfWeek::FRIDAY));
Assert::same($saturday->getDayOfWeekEnum(), DayOfWeek::get(DayOfWeek::SATURDAY));
Assert::same($sunday->getDayOfWeekEnum(), DayOfWeek::get(DayOfWeek::SUNDAY));

// isDayOfWeek()
Assert::true($monday->isDayOfWeek(1));
Assert::true($monday->isDayOfWeek(DayOfWeek::get(DayOfWeek::MONDAY)));
Assert::false($monday->isDayOfWeek(7));
Assert::false($monday->isDayOfWeek(DayOfWeek::get(DayOfWeek::SUNDAY)));
Assert::exception(function () use ($monday) {
    $monday->isDayOfWeek(8);
}, InvalidValueException::class);

// isWeekend()
Assert::false($monday->isWeekend());
Assert::false($friday->isWeekend());
Assert::true($saturday->isWeekend());
Assert::true($sunday->isWeekend());

// getMonthEnum()
Assert::same($monday->getMonthEnum(), Month::get(Month::NOVEMBER));

// isMonth()
Assert::true($monday->isMonth(11));
Assert::true($monday->isMonth(Month::get(Month::NOVEMBER)));
Assert::false($monday->isMonth(12));
Assert::false($monday->isMonth(Month::get(Month::DECEMBER)));
Assert::exception(function () use ($monday) {
    $monday->isMonth(13);
}, InvalidValueException::class);
