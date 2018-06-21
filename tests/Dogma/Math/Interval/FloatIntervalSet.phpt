<?php

namespace Dogma\Tests\Math\Interval;

use Dogma\Math\Interval\FloatInterval;
use Dogma\Math\Interval\FloatIntervalSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$interval = new FloatInterval(1, 5);
$emptyInterval = FloatInterval::empty();

$set = new FloatIntervalSet([$interval]);

$r = function (int $start, int $end, bool $openStart = false, bool $openEnd = false) {
    return new FloatInterval((float) $start, (float) $end, $openStart, $openEnd);
};
$s = function (FloatInterval ...$items) {
    return new FloatIntervalSet($items);
};

// isEmpty()
Assert::true((new FloatIntervalSet([]))->isEmpty());
Assert::true((new FloatIntervalSet([$emptyInterval]))->isEmpty());

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
Assert::equal($s($r(1, 10))->subtract($s($r(3, 4), $r(7, 8))), $s($r(1, 3, false, true), $r(4, 7, true, true), $r(8, 10, true, false)));

// intersect()
Assert::equal($s($r(1, 5), $r(10, 15))->intersect($s($r(4, 12), $r(14, 20))), $s($r(4, 5), $r(10, 12), $r(14, 15)));

$set = $s(FloatInterval::empty(), $r(1, 1), $r(1, 2), $r(1, 3));

// filterByLength()
Assert::equal($set->filterByLength('>', 1), $s($r(1, 3)));
Assert::equal($set->filterByLength('>=', 1), $s($r(1, 2), $r(1, 3)));
Assert::equal($set->filterByLength('=', 1), $s($r(1, 2)));
Assert::equal($set->filterByLength('<>', 1), $s(FloatInterval::empty(), $r(1, 1), $r(1, 3)));
Assert::equal($set->filterByLength('<=', 1), $s(FloatInterval::empty(), $r(1, 1), $r(1, 2)));
Assert::equal($set->filterByLength('<', 1), $s(FloatInterval::empty(), $r(1, 1)));
