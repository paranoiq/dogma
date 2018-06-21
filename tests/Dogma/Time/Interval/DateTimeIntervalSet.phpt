<?php

namespace Dogma\Tests\Math\Interval;

use Dogma\Tester\Assert;
use Dogma\Time\DateTime;
use Dogma\Time\Interval\DateTimeInterval;
use Dogma\Time\Interval\DateTimeIntervalSet;
use Dogma\Time\Seconds;

require_once __DIR__ . '/../../bootstrap.php';

$dt = function (int $day) {
    return DateTime::createFromComponents(2000, 1, $day);
};
$i = function (int $start, int $end, bool $openStart = false, bool $openEnd = true) use ($dt) {
    return new DateTimeInterval($dt($start), $dt($end), $openStart, $openEnd);
};
$s = function (DateTimeInterval ...$items) {
    return new DateTimeIntervalSet($items);
};

$interval = new DateTimeInterval($dt(1), $dt(5), false, true);
$emptyInterval = DateTimeInterval::empty();

$set = new DateTimeIntervalSet([$interval]);

// isEmpty()
Assert::true((new DateTimeIntervalSet([]))->isEmpty());
Assert::true((new DateTimeIntervalSet([$emptyInterval]))->isEmpty());

// equals()
Assert::true($set->equals($s($i(1, 5))));
Assert::false($set->equals($s($i(1, 6))));

// containsValue()
Assert::true($set->containsValue($dt(1)));
Assert::true($set->containsValue($dt(4)));
Assert::false($set->containsValue($dt(5)));
Assert::false($set->containsValue($dt(6)));

// envelope()
Assert::equal($s($i(1, 2), $i(4, 5))->envelope(), $interval);

// normalize()
Assert::equal($s($i(1, 4), $i(2, 5))->normalize(), $set);

// add()
Assert::equal($s($i(1, 2), $i(3, 4), $i(5, 6)), $s($i(1, 2))->add($s($i(3, 4), $i(5, 6))));

// subtract()
Assert::equal($s($i(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $s($i(1, 3), $i(4, 7), $i(8, 10)));

// intersect()
Assert::equal($s($i(1, 5), $i(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $s($i(4, 5), $i(10, 12), $i(14, 15)));

$set = $s(DateTimeInterval::empty(), $i(1, 1), $i(1, 2), $i(1, 3));
$microseconds = Seconds::DAY * 1000000;

// filterByLength()
Assert::equal($set->filterByLength('>', $microseconds), $s($i(1, 3)));
Assert::equal($set->filterByLength('>=', $microseconds), $s($i(1, 2), $i(1, 3)));
Assert::equal($set->filterByLength('=', $microseconds), $s($i(1, 2)));
Assert::equal($set->filterByLength('<>', $microseconds), $s(DateTimeInterval::empty(), $i(1, 1), $i(1, 3)));
Assert::equal($set->filterByLength('<=', $microseconds), $s(DateTimeInterval::empty(), $i(1, 1), $i(1, 2)));
Assert::equal($set->filterByLength('<', $microseconds), $s(DateTimeInterval::empty(), $i(1, 1)));
