<?php

namespace Dogma\Tests\Math\Interval;

use Dogma\Math\Interval\IntInterval;
use Dogma\Math\Interval\IntIntervalSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$interval = new IntInterval(1, 5);
$emptyInterval = IntInterval::empty();

$set = new IntIntervalSet([$interval]);

$r = function (int $start, int $end) {
    return new IntInterval($start, $end);
};
$s = function (IntInterval ...$items) {
    return new IntIntervalSet($items);
};

// isEmpty()
Assert::true((new IntIntervalSet([]))->isEmpty());
Assert::true((new IntIntervalSet([$emptyInterval]))->isEmpty());

// equals()
Assert::true($set->equals($s($r(1, 5))));
Assert::false($set->equals($s($r(1, 6))));

// containsValue()
Assert::true($set->containsValue(1));
Assert::true($set->containsValue(5));
Assert::false($set->containsValue(6));

// envelope()
Assert::equal($s($r(1, 2), $r(4, 5))->envelope(), $interval);

// normalize()
Assert::equal($s($r(1, 4), $r(2, 5))->normalize(), $set);

// add()
Assert::equal($s($r(1, 2), $r(3, 4), $r(5, 6)), $s($r(1, 2))->add($s($r(3, 4), $r(5, 6))));

// subtract()
Assert::equal($s($r(1, 10))->subtract($s($r(3, 4), $r(7, 8))), $s($r(1, 2), $r(5, 6), $r(9, 10)));

// intersect()
Assert::equal($s($r(1, 5), $r(10, 15))->intersect($s($r(4, 12), $r(14, 20))), $s($r(4, 5), $r(10, 12), $r(14, 15)));
