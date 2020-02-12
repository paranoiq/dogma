<?php declare(strict_types = 1);

namespace Dogma\Tests\Time\Interval;

use Dogma\Math\Vector\Vector3i;
use Dogma\Tester\Assert;
use Dogma\Time\Date;
use Dogma\Time\Interval\NightInterval;
use Dogma\Time\Interval\NightIntervalSet;
use Dogma\Time\IntervalData\NightIntervalData;
use Dogma\Time\IntervalData\NightIntervalDataSet;

require_once __DIR__ . '/../../bootstrap.php';

$data = new Vector3i(1, 2, 3);
$data2 = new Vector3i(2, 4, 6);

$d = function (int $day): Date {
    return Date::createFromComponents(2000, 1, $day);
};
$i = function (int $start, int $end) use ($d): NightInterval {
    return new NightInterval($d($start), $d($end));
};
$di = function (int $start, int $end) use ($d, $data): NightIntervalData {
    return new NightIntervalData($d($start), $d($end), $data);
};
$di2 = function (int $start, int $end) use ($d, $data2): NightIntervalData {
    return new NightIntervalData($d($start), $d($end), $data2);
};
$s = function (NightInterval ...$items): NightIntervalSet {
    return new NightIntervalSet($items);
};
$ds = function (NightIntervalData ...$items): NightIntervalDataSet {
    return new NightIntervalDataSet($items);
};

$interval = new NightIntervalData($d(1), $d(5), $data);
$emptyInterval = NightInterval::empty();
$set = new NightIntervalDataSet([$interval]);

// toDateDataArray()
Assert::equal($emptyInterval->toDateArray(), []);
Assert::equal($interval->toDateDataArray(), [[$d(1), $data], [$d(2), $data], [$d(3), $data], [$d(4), $data]]);
Assert::equal($ds($di(1, 3), $di(4, 6))->toDateDataArray(), [[$d(1), $data], [$d(2), $data], [$d(4), $data], [$d(5), $data]]);

// isEmpty()
Assert::true((new NightIntervalSet([]))->isEmpty());
Assert::true((new NightIntervalSet([$emptyInterval]))->isEmpty());

// equals()
Assert::true($set->equals($ds($di(1, 5))));
Assert::false($set->equals($ds($di(1, 6))));

// containsValue()
Assert::true($set->containsValue($d(1)));
Assert::true($set->containsValue($d(4)));
Assert::false($set->containsValue($d(5)));

// normalize()
Assert::equal($ds($di(1, 4), $di(2, 5))->normalize(), $set);
Assert::equal($ds($di(10, 14), $di(5, 10), $di(18, 22), $di(5, 7), $di(15, 20))->normalize(), $ds($di(5, 14), $di(15, 22)));

// add()
Assert::equal($ds($di(1, 2), $di(3, 4), $di(5, 6)), $ds($di(1, 2))->add($ds($di(3, 4), $di(5, 6))));

// subtract()
Assert::equal($ds($di(1, 11))->subtract($s($i(3, 5), $i(7, 9))), $ds($di(1, 3), $di(5, 7), $di(9, 11)));

// intersect()
Assert::equal($ds($di(1, 5), $di(10, 15))->intersect($s($i(4, 12), $i(14, 20))), $ds($di(4, 5), $di(10, 12), $di(14, 15)));

// map()
Assert::equal($set->map(function (NightIntervalData $interval) {
    return $interval;
}), $set);
Assert::equal($set->map(function (NightIntervalData $interval) use ($i) {
    return $interval->subtract($i(3, 4));
}), $ds($di(1, 3), $di(4, 5)));
Assert::equal($set->map(function (NightIntervalData $interval) use ($i) {
    return $interval->subtract($i(3, 4))->getIntervals();
}), $ds($di(1, 3), $di(4, 5)));

// collect()

// collectData()

// modifyData()
$reducer = function (Vector3i $state, Vector3i $change): Vector3i {
    return $state->add($change);
};
Assert::equal($ds($di(10, 16))->modifyData($ds($di(20, 26)), $reducer), $ds($di(10, 16))); // no match
Assert::equal($ds($di(10, 16))->modifyData($ds($di(10, 16)), $reducer), $ds($di2(10, 16))); // same
Assert::equal($ds($di(10, 16))->modifyData($ds($di(10, 13)), $reducer)->normalize(), $ds($di2(10, 13), $di(13, 16))); // same start
Assert::equal($ds($di(10, 16))->modifyData($ds($di(13, 16)), $reducer)->normalize(), $ds($di(10, 13), $di2(13, 16))); // same end
Assert::equal($ds($di(10, 16))->modifyData($ds($di(5, 13)), $reducer)->normalize(), $ds($di2(10, 13), $di(13, 16))); // overlaps start
Assert::equal($ds($di(10, 16))->modifyData($ds($di(13, 21)), $reducer)->normalize(), $ds($di(10, 13), $di2(13, 16))); // overlaps end
Assert::equal($ds($di(10, 16))->modifyData($ds($di(12, 14)), $reducer)->normalize(), $ds($di(10, 12), $di2(12, 14), $di(14, 16))); // in middle
Assert::equal($ds($di(10, 16))->modifyData($ds($di(5, 21)), $reducer), $ds($di2(10, 16))); // overlaps whole

Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(10, 26)), $reducer)->normalize(), $ds($di2(10, 16), $di2(20, 26))); // envelope
Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(10, 23)), $reducer)->normalize(), $ds($di2(10, 16), $di2(20, 23), $di(23, 26))); // same start
Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(13, 26)), $reducer)->normalize(), $ds($di(10, 13), $di2(13, 16), $di2(20, 26))); // same end
Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(5, 23)), $reducer)->normalize(), $ds($di2(10, 16), $di2(20, 23), $di(23, 26))); // overlaps start
Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(13, 26)), $reducer)->normalize(), $ds($di(10, 13), $di2(13, 16), $di2(20, 26))); // overlaps end
Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(13, 23)), $reducer)->normalize(), $ds($di(10, 13), $di2(13, 16), $di2(20, 23), $di(23, 26))); // in middle
Assert::equal($ds($di(10, 16), $di(20, 26))->modifyData($ds($di(5, 31)), $reducer)->normalize(), $ds($di2(10, 16), $di2(20, 26))); // overlaps whole
