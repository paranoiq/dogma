<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Math\Interval\InvalidIntervalStringFormatException;
use Dogma\Str;
use Dogma\Tester\Assert;
use Dogma\Time\Interval\TimeInterval;
use Dogma\Time\Interval\TimeIntervalSet;
use Dogma\Time\Microseconds;
use Dogma\Time\Span\DateTimeSpan;
use Dogma\Time\Span\TimeSpan;
use Dogma\Time\Time;

require_once __DIR__ . '/../../bootstrap.php';

$startTime = new Time('10:00:00.000000');
$endTime = new Time('20:00:00.000000');
$interval = new TimeInterval($startTime, $endTime);

$empty = TimeInterval::empty();
$all = TimeInterval::all();

$t = function (int $hour) {
    return new Time(Str::padLeft($hour, 2, '0') . ':00:00.000000');
};
$r = function (int $start, int $end, bool $openStart = false, bool $openEnd = true) {
    return new TimeInterval(
        new Time(Str::padLeft($start, 2, '0') . ':00:00.000000'),
        new Time(Str::padLeft($end, 2, '0') . ':00:00.000000'),
        $openStart,
        $openEnd
    );
};
$s = function (TimeInterval ...$items) {
    return new TimeIntervalSet($items);
};

// createFromString()
Assert::equal(TimeInterval::createFromString('10:00,20:00'), $interval);
Assert::equal(TimeInterval::createFromString('10:00|20:00'), $interval);
Assert::equal(TimeInterval::createFromString('10:00/20:00'), $interval);
Assert::equal(TimeInterval::createFromString('10:00 - 20:00'), $interval);
Assert::equal(TimeInterval::createFromString('[10:00,20:00]'), new TimeInterval($startTime, $endTime, false, false));
Assert::equal(TimeInterval::createFromString('[10:00,20:00)'), $interval);
Assert::equal(TimeInterval::createFromString('(10:00,20:00)'), new TimeInterval($startTime, $endTime, true, true));
Assert::equal(TimeInterval::createFromString('(10:00,20:00]'), new TimeInterval($startTime, $endTime, true, false));
Assert::exception(function () {
    TimeInterval::createFromString('foo|bar|baz');
}, InvalidIntervalStringFormatException::class);
Assert::exception(function () {
    TimeInterval::createFromString('foo');
}, InvalidIntervalStringFormatException::class);

// shift()
Assert::equal($interval->shift('+1 hour'), $r(11, 21));

// getStart()
Assert::equal($interval->getStart(), new Time('10:00:00.000000'));

// getEnd()
Assert::equal($interval->getEnd(), new Time('20:00:00.000000'));

// getSpan()
Assert::equal($interval->getSpan(), new DateTimeSpan(0, 0, 0, 10));

// getTimeSpan()
Assert::equal($interval->getTimeSpan(), new TimeSpan(10));

// getLengthInMicroseconds()
Assert::same($interval->getLengthInMicroseconds(), Microseconds::HOUR * 10);
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
Assert::true($interval->containsValue($t(10)));
Assert::true($interval->containsValue($t(15)));
Assert::true($interval->containsValue($t(19)));
Assert::false($interval->containsValue($t(5)));
Assert::false($interval->containsValue($t(20)));

// contains()
Assert::true($interval->contains($r(10, 20)));
Assert::true($interval->contains($r(10, 15)));
Assert::true($interval->contains($r(15, 20)));
Assert::false($interval->contains($r(5, 20)));
Assert::false($interval->contains($r(10, 23)));
Assert::false($interval->contains($r(1, 5)));
Assert::false($interval->contains($empty));

// intersects()
Assert::true($interval->intersects($r(10, 20)));
Assert::true($interval->intersects($r(5, 15)));
Assert::true($interval->intersects($r(15, 23)));
Assert::false($interval->intersects($r(1, 5)));
Assert::false($interval->intersects($empty));

// touches()
Assert::true($interval->touches($r(1, 10)));
Assert::true($interval->touches($r(20, 23)));
Assert::false($interval->touches($r(1, 11)));
Assert::false($interval->touches($r(19, 23)));
Assert::false($interval->touches($r(21, 23)));
Assert::false($interval->touches($empty));

// split()
$splitMode = TimeInterval::SPLIT_OPEN_ENDS;
Assert::equal($interval->split(1, $splitMode), $s($interval));
Assert::equal($interval->split(2, $splitMode), $s($r(10, 15), $r(15, 20)));
Assert::equal($interval->split(3, $splitMode), $s(
    TimeInterval::openEnd(new Time('10:00:00'), new Time('13:20:00')),
    TimeInterval::openEnd(new Time('13:20:00'), new Time('16:40:00')),
    TimeInterval::openEnd(new Time('16:40:00'), new Time('20:00:00'))
));
Assert::equal($empty->split(5, $splitMode), $s());

// splitBy()
Assert::equal($interval->splitBy([$t(5), $t(15), $t(25)], $splitMode), $s($r(10, 15), $r(15, 20)));
Assert::equal($empty->splitBy([$t(5)], $splitMode), $s());

// envelope()
Assert::equal($interval->envelope($r(5, 15)), $r(5, 20));
Assert::equal($interval->envelope($r(15, 25)), $r(10, 25));
Assert::equal($interval->envelope($r(1, 5)), $r(1, 20));
Assert::equal($interval->envelope($r(21, 25)), $r(10, 25));
Assert::equal($interval->envelope($r(4, 5), $r(21, 25)), $r(4, 25));
Assert::equal($interval->envelope($empty), $interval);

// intersect()
Assert::equal($interval->intersect($r(1, 15)), $r(10, 15));
Assert::equal($interval->intersect($r(15, 25)), $r(15, 20));
Assert::equal($interval->intersect($r(1, 18), $r(14, 25)), $r(14, 18));
Assert::equal($interval->intersect($r(1, 5)), $empty);
Assert::equal($interval->intersect($r(1, 5), $r(5, 15)), $empty);
Assert::equal($interval->intersect($empty), $empty);

// union()
Assert::equal($interval->union($r(1, 15)), $s($r(1, 20)));
Assert::equal($interval->union($r(15, 25)), $s($r(10, 25)));
Assert::equal($interval->union($r(4, 15), $r(15, 25)), $s($r(4, 25)));
Assert::equal($interval->union($r(25, 26)), $s($r(25, 26), $interval));
Assert::equal($interval->union($all), $s($all));
Assert::equal($interval->union($empty), $s($interval));

// difference()
Assert::equal($interval->difference($r(15, 25)), $s($r(10, 15), $r(20, 25)));
Assert::equal($interval->difference($r(5, 15)), $s($r(5, 10), $r(15, 20)));
Assert::equal($interval->difference($r(5, 15), $r(15, 25)), $s($r(5, 10), $r(20, 25)));
Assert::equal($interval->difference($r(22, 25)), $s($interval, $r(22, 25)));
Assert::equal($interval->difference($all), $s(new TimeInterval(new Time(Time::MIN), $t(10), false, true), new TimeInterval($t(20), new Time(Time::MAX), false, false)));
Assert::equal($interval->difference($empty), $s($interval));

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
Assert::equal($interval->invert(), $s(new TimeInterval(new Time(Time::MIN), $t(10), false, true), new TimeInterval($t(20), new Time(Time::MAX), false, false)));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s($empty));

// countOverlaps()
Assert::equal(TimeInterval::countOverlaps($empty), []);
Assert::equal(TimeInterval::countOverlaps($interval, $r(5, 15)), [
    [$r(5, 10), 1],
    [$r(10, 15), 2],
    [$r(15, 20), 1],
]);
Assert::equal(TimeInterval::countOverlaps($r(5, 15, false, false), $r(10, 20, false, false), $r(15, 25, false, false)), [
    [$r(5, 10, false, true), 1],
    [$r(10, 15, false, true), 2],
    [$r(15, 15, false, false), 3],
    [$r(15, 20, true, false), 2],
    [$r(20, 25, true, false), 1],
]);

// explodeOverlaps()
Assert::equal(TimeInterval::explodeOverlaps($empty), []);
Assert::equal($s(...TimeInterval::explodeOverlaps($interval, $r(5, 15))), $s(
    $r(5, 10),
    $r(10, 15),
    $r(10, 15),
    $r(15, 20)
));
Assert::equal($s(...TimeInterval::explodeOverlaps($r(5, 15, false, false), $r(10, 20, false, false), $r(15, 25, false, false))), $s(
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
