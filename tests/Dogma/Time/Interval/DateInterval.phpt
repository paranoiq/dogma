<?php

namespace Dogma\Tests\Time\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;

require_once __DIR__ . '/../../bootstrap.php';

$startDate = new Date('2000-01-10');
$endDate = new Date('2000-01-20');
$interval = new DateInterval($startDate, $endDate);
$empty = DateInterval::empty();
$all = DateInterval::all();

$d = function (int $day) {
    return new Date('2000-01-' . $day);
};
$r = function (int $start, int $end) {
    return new DateInterval(new Date('2000-01-' . $start), new Date('2000-01-' . $end));
};
$s = function (DateInterval ...$items) {
    return new DateIntervalSet($items);
};

// shift()
Assert::equal($interval->shift('+1 day'), $r(11, 21));

// getStart()
Assert::equal($interval->getStart(), new Date('2000-01-10'));

// getEnd()
Assert::equal($interval->getEnd(), new Date('2000-01-20'));

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
Assert::true($interval->containsValue($d(20)));
Assert::false($interval->containsValue($d(5)));
Assert::false($interval->containsValue($d(25)));
Assert::true($interval->containsValue(new \DateTimeImmutable('2000-01-15')));

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
Assert::true($interval->touches($r(1, 9)));
Assert::true($interval->touches($r(21, 25)));
Assert::false($interval->touches($r(1, 10)));
Assert::false($interval->touches($r(20, 25)));
Assert::false($interval->touches($empty));

// split()
Assert::equal($interval->split(1), $s($interval));
Assert::equal($interval->split(2), $s($r(10, 15), $r(16, 20)));
Assert::equal($interval->split(3), $s($r(10, 13), $r(14, 16), $r(17, 20)));
Assert::equal($interval->split(4), $s($r(10, 12), $r(13, 15), $r(16, 17), $r(18, 20)));
Assert::equal($interval->split(11), $s($r(10, 10), $r(11, 11), $r(12, 12), $r(13, 13), $r(14, 14), $r(15, 15), $r(16, 16), $r(17, 17), $r(18, 18), $r(19, 19), $r(20, 20)));
Assert::equal($empty->split(5), $s());

// splitBy()
Assert::equal($interval->splitBy([$d(5), $d(15), $d(25)]), $s($r(10, 14), $r(15, 20)));
Assert::equal($empty->splitBy([$d(5)]), $s());

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
Assert::equal($interval->difference($r(15, 30)), $s($r(10, 14), $r(21, 30)));
Assert::equal($interval->difference($r(5, 15)), $s($r(5, 9), $r(16, 20)));
Assert::equal($interval->difference($r(5, 15), $r(15, 30)), $s($r(5, 9), $r(21, 30)));
Assert::equal($interval->difference($r(25, 30)), $s($interval, $r(25, 30)));
Assert::equal($interval->difference($all), $s(new DateInterval(new Date(Date::MIN), $d(9)), new DateInterval($d(21), new Date(Date::MAX))));
Assert::equal($interval->difference($empty), $s($interval));

// subtract()
Assert::equal($interval->subtract($r(5, 15)), $s($r(16, 20)));
Assert::equal($interval->subtract($r(15, 25)), $s($r(10, 14)));
Assert::equal($interval->subtract($r(13, 17)), $s($r(10, 12), $r(18, 20)));
Assert::equal($interval->subtract($r(5, 10), $r(20, 25)), $s($r(11, 19)));
Assert::equal($interval->subtract($empty), $s($interval));
Assert::equal($interval->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s());

// invert()
Assert::equal($interval->invert(), $s(new DateInterval(new Date(Date::MIN), $d(9)), new DateInterval($d(21), new Date(Date::MAX))));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));

// countOverlaps()
Assert::equal(DateInterval::countOverlaps($empty), []);
Assert::equal(DateInterval::countOverlaps($interval, $r(5, 15)), [
    [$r(5, 9), 1],
    [$r(10, 15), 2],
    [$r(16, 20), 1],
]);
Assert::equal(DateInterval::countOverlaps($r(5, 15), $r(10, 20), $r(15, 25)), [
    [$r(5, 9), 1],
    [$r(10, 14), 2],
    [$r(15, 15), 3],
    [$r(16, 20), 2],
    [$r(21, 25), 1],
]);

// explodeOverlaps()
Assert::equal(DateInterval::explodeOverlaps($empty), []);
Assert::equal($s(...DateInterval::explodeOverlaps($interval, $r(5, 15))), $s(
    $r(5, 9),
    $r(10, 15),
    $r(10, 15),
    $r(16, 20)
));
Assert::equal($s(...DateInterval::explodeOverlaps($r(5, 15), $r(10, 20), $r(15, 25))), $s(
    $r(5, 9),
    $r(10, 14),
    $r(10, 14),
    $r(15, 15),
    $r(15, 15),
    $r(15, 15),
    $r(16, 20),
    $r(16, 20),
    $r(21, 25)
));
