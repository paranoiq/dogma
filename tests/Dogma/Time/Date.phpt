<?php

namespace Dogma\Tests\Time;

use Dogma\Time\Date;
use Dogma\Time\InvalidDateTimeException;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$dateString = '2000-01-02';
$date = new Date($dateString);
$dateTime = new \DateTime($dateString);
$dateTimeImmutable = new \DateTimeImmutable($dateString);
$timestamp = 946782245;
$utcTimeZone = new \DateTimeZone('UTC');

// __construct()
Assert::throws(function () {
    new Date('asdf');
}, InvalidDateTimeException::class);
Assert::type(new Date(), Date::class);
Assert::same((new Date('today'))->format(), date(Date::DEFAULT_FORMAT));

// createFromTimestamp()
Assert::type(Date::createFromTimestamp($timestamp), Date::class);
Assert::same(Date::createFromTimestamp($timestamp)->format(), $dateString);

// createFromDateTimeInterface()
Assert::type(Date::createFromDateTimeInterface($dateTime), Date::class);
Assert::type(Date::createFromDateTimeInterface($dateTimeImmutable), Date::class);
Assert::same(Date::createFromDateTimeInterface($dateTime)->format(), $dateString);
Assert::same(Date::createFromDateTimeInterface($dateTimeImmutable)->format(), $dateString);

// createFromComponents()
Assert::type(Date::createFromComponents(2001, 2, 3), Date::class);
Assert::same(Date::createFromComponents(2001, 2, 3)->format('Y-m-d'), '2001-02-03');

// format()
Assert::same($date->format('j.n.Y'), date('j.n.Y', $timestamp));

// getStart()
Assert::same($date->getStart($utcTimeZone)->format(), '2000-01-02 00:00:00');

// getEnd()
Assert::same($date->getEnd($utcTimeZone)->format(), '2000-01-02 23:59:59');

// getStartFormatted()
Assert::same($date->getStartFormatted(null, $utcTimeZone), '2000-01-02 00:00:00');

// getEndFormatted()
Assert::same($date->getEndFormatted(null, $utcTimeZone), '2000-01-02 23:59:59');

$today = new Date('today 12:00');
$today2 = new Date('today 13:00');
$yesterday = new Date('yesterday');
$tomorrow = new Date('tomorrow');

// diff()
Assert::same($today->diff($today)->format('%R %y %m %d %h %i %s'), '+ 0 0 0 0 0 0');
Assert::same($today->diff($today2)->format('%R %y %m %d %h %i %s'), '+ 0 0 0 0 0 0');
Assert::same($today->diff($tomorrow)->format('%R %y %m %d %h %i %s'), '+ 0 0 1 0 0 0');
Assert::same($today->diff($yesterday)->format('%R %y %m %d %h %i %s'), '- 0 0 1 0 0 0');
Assert::same($today->diff($tomorrow, true)->format('%R %y %m %d %h %i %s'), '+ 0 0 1 0 0 0');
Assert::same($today->diff($yesterday, true)->format('%R %y %m %d %h %i %s'), '+ 0 0 1 0 0 0');

// compare()
Assert::same($today->compare($yesterday), 1);
Assert::same($today->compare($today), 0);
Assert::same($today->compare($tomorrow), -1);

// isEqual()
Assert::false($today->isEqual($yesterday));
Assert::false($today->isEqual($tomorrow));
Assert::true($today->isEqual($today));
Assert::true($today->isEqual($today2));

// isBefore()
Assert::false($today->isBefore($yesterday));
Assert::false($today->isBefore($today));
Assert::true($today->isBefore($tomorrow));

// isAfter()
Assert::true($today->isAfter($yesterday));
Assert::false($today->isAfter($today));
Assert::false($today->isAfter($tomorrow));

// isBetween()
Assert::false($yesterday->isBetween($today, $tomorrow));
Assert::false($tomorrow->isBetween($today, $yesterday));
Assert::true($yesterday->isBetween($yesterday, $tomorrow));
Assert::true($today->isBetween($yesterday, $tomorrow));
Assert::true($tomorrow->isBetween($yesterday, $tomorrow));

// isFuture()
Assert::false($yesterday->isFuture());
Assert::true($tomorrow->isFuture());

// isPast()
Assert::false($tomorrow->isPast());
Assert::true($yesterday->isPast());
