<?php

namespace Dogma\Tests\Time\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\DateTime;
use Dogma\Time\Seconds;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Interval\DateTimeIntervalSet;
use Dogma\Time\InvalidIntervalException;

require_once __DIR__ . '/../../bootstrap.php';

$startDate = new DateTime('2000-01-10 00:00:00.000000');
$endDate = new DateTime('2000-01-20 00:00:00.000000');
$interval = DateTimeInterval::openEnd($startDate, $endDate);
$empty = DateTimeInterval::empty();
$all = DateTimeInterval::all();

$d = function (int $day) {
    return new DateTime('2000-01-' . $day . ' 00:00:00');
};
$r = function (int $start, int $end, bool $openStart = false, bool $openEnd = true) {
    return new DateTimeInterval(
        new DateTime('2000-01-' . $start . ' 00:00:00.000000'),
        new DateTime('2000-01-' . $end . ' 00:00:00.000000'),
        $openStart,
        $openEnd
    );
};
$s = function (DateTimeInterval ...$items) {
    return new DateTimeIntervalSet($items);
};

// __construct()
Assert::exception(function () {
    new DateTimeInterval(new DateTime('today'), new DateTime('yesterday'));
}, InvalidIntervalException::class);

// shift()
Assert::equal($interval->shift('+1 day'), $r(11, 21));

// getStart()
Assert::equal($interval->getStart(), new DateTime('2000-01-10 00:00:00.000000'));

// getEnd()
Assert::equal($interval->getEnd(), new DateTime('2000-01-20 00:00:00.000000'));

// getSpan()
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 10));

// getLengthInMicroseconds()
Assert::same($interval->getLengthInMicroseconds(), Seconds::DAY * 10 * 1000000);
Assert::same($empty->getLengthInMicroseconds(), 0);

// isEmpty()
Assert::false($interval->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());

// equals()
Assert::true($interval->equals($r(10, 20)));
Assert::false($interval->equals($r(10, 15)));
Assert::false($interval->equals($r(15, 20)));

// containsValue()
Assert::true($interval->containsValue($d(10)));
Assert::true($interval->containsValue($d(15)));
Assert::true($interval->containsValue($d(19)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(20)));

// containsDateTime()
Assert::true($interval->containsDateTime(new \DateTime('2000-01-15')));
Assert::true($interval->containsDateTime(new \DateTimeImmutable('2000-01-15')));

// contains()
Assert::true($interval->contains($r(10, 20)));
Assert::true($interval->contains($r(10, 15)));
Assert::true($interval->contains($r(15, 20)));
Assert::false($interval->contains($r(5, 20)));
Assert::false($interval->contains($r(10, 25)));
Assert::false($interval->contains($r(1, 5)));
Assert::false($interval->contains($empty));

// intersects()
Assert::true($interval->intersects($r(10, 20)));
Assert::true($interval->intersects($r(5, 15)));
Assert::true($interval->intersects($r(15, 25)));
Assert::false($interval->intersects($r(1, 5)));
Assert::false($interval->intersects($empty));

// touches()
Assert::true($interval->touches($r(1, 10)));
Assert::true($interval->touches($r(20, 25)));
Assert::false($interval->touches($r(1, 11)));
Assert::false($interval->touches($r(19, 25)));
Assert::false($interval->touches($r(21, 25)));
Assert::false($interval->touches($empty));

// split()
$splitMode = DateTimeInterval::SPLIT_OPEN_ENDS;
Assert::equal($interval->split(1, $splitMode), $s($interval));
Assert::equal($interval->split(2, $splitMode), $s($r(10, 15), $r(15, 20)));
Assert::equal($interval->split(3, $splitMode), $s(
    DateTimeInterval::openEnd(new DateTime('2000-01-10 00:00:00'), new DateTime('2000-01-13 08:00:00')),
    DateTimeInterval::openEnd(new DateTime('2000-01-13 08:00:00'), new DateTime('2000-01-16 16:00:00')),
    DateTimeInterval::openEnd(new DateTime('2000-01-16 16:00:00'), new DateTime('2000-01-20 00:00:00'))
));
Assert::equal($empty->split(5, $splitMode), $s());

// splitBy()
Assert::equal($interval->splitBy([$d(5), $d(15), $d(25)], $splitMode), $s($r(10, 15), $r(15, 20)));
Assert::equal($empty->splitBy([$d(5)], $splitMode), $s());

// envelope()
Assert::equal($interval->envelope($r(5, 15)), $r(5, 20));
Assert::equal($interval->envelope($r(15, 25)), $r(10, 25));
Assert::equal($interval->envelope($r(1, 5)), $r(1, 20));
Assert::equal($interval->envelope($r(25, 30)), $r(10, 30));
Assert::equal($interval->envelope($r(1, 5), $r(25, 30)), $r(1, 30));
Assert::equal($interval->envelope($empty), $interval);

// intersect()
Assert::equal($interval->intersect($r(1, 15)), $r(10, 15));
Assert::equal($interval->intersect($r(15, 30)), $r(15, 20));
Assert::equal($interval->intersect($r(1, 18), $r(14, 30)), $r(14, 18));
Assert::equal($interval->intersect($r(1, 5)), $empty);
Assert::equal($interval->intersect($r(1, 5), $r(5, 15)), $empty);
Assert::equal($interval->intersect($empty), $empty);

// union()
Assert::equal($interval->union($r(1, 15)), $s($r(1, 20)));
Assert::equal($interval->union($r(15, 30)), $s($r(10, 30)));
Assert::equal($interval->union($r(1, 15), $r(15, 30)), $s($r(1, 30)));
Assert::equal($interval->union($r(25, 30)), $s($interval, $r(25, 30)));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));

// difference()
/*
Assert::equal($interval->difference($r(15, 30)), $s($r(10, 15), $r(20, 30)));
Assert::equal($interval->difference($r(5, 15)), $s($r(5, 10), $r(15, 20)));
Assert::equal($interval->difference($r(5, 15), $r(15, 30)), $s($r(5, 10), $r(20, 30)));
Assert::equal($interval->difference($r(25, 30)), $s($interval, $r(25, 30)));
Assert::equal($interval->difference($all), $s(new DateTimeInterval(new DateTime(DateTime::MIN), $d(10)), new DateTimeInterval($d(20), new DateTime(DateTime::MAX))));
Assert::equal($interval->difference($empty), $s($interval));
*/
// subtract()
Assert::equal($interval->subtract($r(5, 15)), $s($r(15, 20)));
Assert::equal($interval->subtract($r(15, 25)), $s($r(10, 15)));
Assert::equal($interval->subtract($r(13, 17)), $s($r(10, 13), $r(17, 20)));
Assert::equal($interval->subtract($r(5, 10), $r(20, 25)), $s($r(10, 20)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s());

// invert()
Assert::equal($interval->invert(), $s(new DateTimeInterval(new DateTime(DateTime::MIN), $d(10), false, true), new DateTimeInterval($d(20), new DateTime(DateTime::MAX))));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));

// countOverlaps()
Assert::equal(DateTimeInterval::countOverlaps($empty), []);
Assert::equal(DateTimeInterval::countOverlaps($interval, $r(5, 15)), [
    [$r(5, 10), 1],
    [$r(10, 15), 2],
    [$r(15, 20), 1],
]);
Assert::equal(DateTimeInterval::countOverlaps($r(5, 15, false, false), $r(10, 20, false, false), $r(15, 25, false, false)), [
    [$r(5, 10, false, true), 1],
    [$r(10, 15, false, true), 2],
    [$r(15, 15, false, false), 3],
    [$r(15, 20, true, false), 2],
    [$r(20, 25, true, false), 1],
]);

// explodeOverlaps()
Assert::equal(DateTimeInterval::explodeOverlaps($empty), []);
Assert::equal($s(...DateTimeInterval::explodeOverlaps($interval, $r(5, 15))), $s(
    $r(5, 10),
    $r(10, 15),
    $r(10, 15),
    $r(15, 20)
));
Assert::equal($s(...DateTimeInterval::explodeOverlaps($r(5, 15, false, false), $r(10, 20, false, false), $r(15, 25, false, false))), $s(
    $r(5, 10, false, true),
    $r(10, 15, false, true),
    $r(10, 15, false, true),
    $r(15, 15, false, false),
    $r(15, 15, false, false),
    $r(15, 15, false, false),
    $r(15, 20, true, false),
    $r(15, 20, true, false),
    $r(20, 25, true, false)
));
