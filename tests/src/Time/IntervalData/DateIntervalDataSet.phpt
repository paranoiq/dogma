<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Math\Vector\Vector3i;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\DateInterval;
use Dogma\Time\Interval\DateIntervalSet;
use Dogma\Time\IntervalData\DateIntervalData;
use Dogma\Time\IntervalData\DateIntervalDataSet;

require_once __DIR__ . '/../../bootstrap.php';

$data = new Vector3i(1, 2, 3);
$data2 = new Vector3i(2, 4, 6);

$d = function (int $day): Date {
    return Date::createFromComponents(2000, 1, $day);
};
$i = function (int $start, int $end) use ($d): DateInterval {
    return new DateInterval($d($start), $d($end));
};
$di = function (int $start, int $end) use ($d, $data): DateIntervalData {
    return new DateIntervalData($d($start), $d($end), $data);
};
$di2 = function (int $start, int $end) use ($d, $data2): DateIntervalData {
    return new DateIntervalData($d($start), $d($end), $data2);
};
$s = function (DateInterval ...$items): DateIntervalSet {
    return new DateIntervalSet($items);
};
$ds = function (DateIntervalData ...$items): DateIntervalDataSet {
    return new DateIntervalDataSet($items);
};

$interval = new DateIntervalData($d(1), $d(5), $data);
$emptyInterval = DateInterval::empty();
$set = new DateIntervalDataSet([$interval]);

// toDateDataArray()
Assert::equal($emptyInterval->toDateArray(), []);
Assert::equal($interval->toDateDataArray(), [[$d(1), $data], [$d(2), $data], [$d(3), $data], [$d(4), $data], [$d(5), $data]]);
Assert::equal($ds($di(1, 2), $di(4, 5))->toDateDataArray(), [[$d(1), $data], [$d(2), $data], [$d(4), $data], [$d(5), $data]]);

// isEmpty()
Assert::true((new DateIntervalSet([]))->isEmpty());
Assert::true((new DateIntervalSet([$emptyInterval]))->isEmpty());

// equals()
Assert::true($set->equals($ds($di(1, 5))));
Assert::false($set->equals($ds($di(1, 6))));

// containsValue()
Assert::true($set->containsValue($d(1)));
Assert::true($set->containsValue($d(5)));
Assert::false($set->containsValue($d(6)));

// normalize()
Assert::equal($ds($di(1, 4), $di(2, 5))->normalize(), $set);
Assert::equal($ds($di(10, 13), $di(5, 9), $di(18, 21), $di(5, 6), $di(15, 19))->normalize(), $ds($di(5, 13), $di(15, 21)));

// add()
Assert::equal($ds($di(1, 2), $di(3, 4), $di(5, 6)), $ds($di(1, 2))->add($ds($di(3, 4), $di(5, 6))));

// subtract()
Assert::equal($ds($di(1, 10))->subtract($s($i(3, 4), $i(7, 8))), $ds($di(1, 2), $di(5, 6), $di(9, 10)));

// intersect()
Assert::equal($ds($di(1, 5), $di(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $ds($di(4, 5), $di(10, 12), $di(14, 15)));

// map()
Assert::equal($set->map(function (DateIntervalData $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(function (DateIntervalData $interval) use ($i) {
    return $interval->subtract($i(3, 3));
}), $ds($di(1, 2), $di(4, 5)));
Assert::equal($set->map(function (DateIntervalData $interval) use ($i) {
    return $interval->subtract($i(3, 3))->getIntervals();
}), $ds($di(1, 2), $di(4, 5)));

// collect()

// collectData()

// modifyData()
$reducer = function (Vector3i $state, Vector3i $change): Vector3i {
    return $state->add($change);
};
Assert::equal($ds($di(10, 15))->modifyData($ds($di(20, 25)), $reducer), $ds($di(10, 15))); // no match
Assert::equal($ds($di(10, 15))->modifyData($ds($di(10, 15)), $reducer), $ds($di2(10, 15))); // same
Assert::equal($ds($di(10, 15))->modifyData($ds($di(10, 12)), $reducer)->normalize(), $ds($di2(10, 12), $di(13, 15))); // same start
Assert::equal($ds($di(10, 15))->modifyData($ds($di(13, 15)), $reducer)->normalize(), $ds($di(10, 12), $di2(13, 15))); // same end
Assert::equal($ds($di(10, 15))->modifyData($ds($di(5, 12)), $reducer)->normalize(), $ds($di2(10, 12), $di(13, 15))); // overlaps start
Assert::equal($ds($di(10, 15))->modifyData($ds($di(13, 20)), $reducer)->normalize(), $ds($di(10, 12), $di2(13, 15))); // overlaps end
Assert::equal($ds($di(10, 15))->modifyData($ds($di(12, 13)), $reducer)->normalize(), $ds($di(10, 11), $di2(12, 13), $di(14, 15))); // in middle
Assert::equal($ds($di(10, 15))->modifyData($ds($di(5, 20)), $reducer), $ds($di2(10, 15))); // overlaps whole

Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(10, 25)), $reducer)->normalize(), $ds($di2(10, 15), $di2(20, 25))); // envelope
Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(10, 22)), $reducer)->normalize(), $ds($di2(10, 15), $di2(20, 22), $di(23, 25))); // same start
Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(13, 25)), $reducer)->normalize(), $ds($di(10, 12), $di2(13, 15), $di2(20, 25))); // same end
Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(5, 22)), $reducer)->normalize(), $ds($di2(10, 15), $di2(20, 22), $di(23, 25))); // overlaps start
Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(13, 25)), $reducer)->normalize(), $ds($di(10, 12), $di2(13, 15), $di2(20, 25))); // overlaps end
Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(13, 22)), $reducer)->normalize(), $ds($di(10, 12), $di2(13, 15), $di2(20, 22), $di(23, 25))); // in middle
Assert::equal($ds($di(10, 15), $di(20, 25))->modifyData($ds($di(5, 30)), $reducer)->normalize(), $ds($di2(10, 15), $di2(20, 25))); // overlaps whole
