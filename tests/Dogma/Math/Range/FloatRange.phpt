<?php

namespace Dogma\Tests\Math\Range;

use Dogma\Math\Range\FloatRange;
use Dogma\Math\Range\FloatRangeSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$inclusive = new FloatRange(1.0, 5.0);
$exclusive = new FloatRange(1.0, 5.0, true, true);
$empty = FloatRange::createEmpty();
$all = FloatRange::createAll();

$r = function (int $start, int $end, bool $startExclusive = false, bool $endExclusive = false) {
    return new FloatRange((float) $start, (float) $end, $startExclusive, $endExclusive);
};
$s = function (FloatRange ...$items) {
    return new FloatRangeSet($items);
};

// shift()
Assert::equal($inclusive->shift(10.0), $r(11, 15));
Assert::equal($exclusive->shift(10.0), $r(11, 15, true, true));

// multiply()
Assert::equal($inclusive->multiply(10.0), $r(10, 50));
Assert::equal($exclusive->multiply(10.0), $r(10, 50, true, true));

// getStart()
Assert::same($inclusive->getStart(), 1.0);
Assert::same($exclusive->getStart(), 1.0);

// getEnd()
Assert::same($inclusive->getEnd(), 5.0);
Assert::same($exclusive->getEnd(), 5.0);

// isStartExclusive()
Assert::false($inclusive->isStartExclusive());
Assert::true($exclusive->isStartExclusive());

// isEndExclusive()
Assert::false($inclusive->isEndExclusive());
Assert::true($exclusive->isEndExclusive());

// isEmpty()
Assert::false($inclusive->isEmpty());
Assert::false($exclusive->isEmpty());

Assert::false((new FloatRange(1.0, 1.0))->isEmpty());
Assert::true((new FloatRange(1.0, 1.0, true, true))->isEmpty());

Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());

// equals()
Assert::true($inclusive->equals($r(1, 5)));
Assert::false($inclusive->equals($r(1, 4)));
Assert::false($inclusive->equals($r(2, 5)));

Assert::true($exclusive->equals($r(1, 5, true, true)));
Assert::false($exclusive->equals($r(1, 5)));
Assert::false($exclusive->equals($r(1, 5, true)));
Assert::false($exclusive->equals($r(1, 5, false, true)));

Assert::true($empty->equals(new FloatRange(1.0, 1.0, true, true)));

// containsValue()
Assert::true($inclusive->containsValue(3.0));
Assert::true($inclusive->containsValue(1.0));
Assert::true($inclusive->containsValue(5.0));
Assert::false($inclusive->containsValue(0.0));
Assert::false($inclusive->containsValue(6.0));

Assert::true($exclusive->containsValue(3.0));
Assert::false($exclusive->containsValue(1.0));
Assert::false($exclusive->containsValue(5.0));
Assert::false($exclusive->containsValue(0.0));
Assert::false($exclusive->containsValue(6.0));

// contains()
Assert::true($inclusive->contains($r(1, 5)));
Assert::true($inclusive->contains($r(1, 3)));
Assert::true($inclusive->contains($r(3, 5)));
Assert::false($inclusive->contains($r(0, 5)));
Assert::false($inclusive->contains($r(1, 6)));
Assert::false($inclusive->contains($r(-1, 0)));
Assert::false($inclusive->contains($empty));

Assert::false($exclusive->contains($r(1, 5)));
Assert::false($exclusive->contains($r(1, 3)));
Assert::false($exclusive->contains($r(3, 5)));
Assert::false($exclusive->contains($r(1, 5, true)));
Assert::false($exclusive->contains($r(1, 5, false, true)));
Assert::true($exclusive->contains($r(1, 5, true, true)));

// intersects()
Assert::true($inclusive->intersects($r(1, 5)));
Assert::true($inclusive->intersects($r(0, 1)));
Assert::true($inclusive->intersects($r(5, 6)));
Assert::false($inclusive->intersects($r(-1, 0)));
Assert::false($inclusive->intersects($empty));

Assert::true($exclusive->intersects($r(1, 5)));
Assert::true($exclusive->intersects($r(0, 1)));
Assert::true($exclusive->intersects($r(5, 6)));
Assert::false($exclusive->intersects($r(0, 1, false, true)));
Assert::false($exclusive->intersects($r(5, 6, true)));

// touches()
Assert::true($inclusive->touches($r(-10, 1)));
Assert::true($inclusive->touches($r(5, 10)));
Assert::false($inclusive->touches($r(-10, 2)));
Assert::false($inclusive->touches($r(6, 10)));

Assert::false($inclusive->touches($r(-10, 1), true));
Assert::false($inclusive->touches($r(5, 10), true));
Assert::true($inclusive->touches($r(-10, 1, false, true), true));
Assert::true($inclusive->touches($r(5, 10, true, false), true));
Assert::false($exclusive->touches($r(-10, 1, false, true), true));
Assert::false($exclusive->touches($r(5, 10, true, false), true));

// split()
Assert::equal($inclusive->split(1), $s($inclusive));
Assert::equal($inclusive->split(2), $s($r(1, 3), $r(3, 5)));
Assert::equal(
    $inclusive->split(2, FloatRange::SPLIT_EXCLUSIVE_STARTS),
    $s($r(1, 3), $r(3, 5, true))
);
Assert::equal(
    $inclusive->split(2, FloatRange::SPLIT_EXCLUSIVE_ENDS),
    $s($r(1, 3, false, true), $r(3, 5))
);
Assert::equal(
    $inclusive->split(4),
    $s($r(1, 2), $r(2, 3), $r(3, 4), $r(4, 5))
);
Assert::equal(
    $inclusive->split(4, FloatRange::SPLIT_EXCLUSIVE_STARTS),
    $s($r(1, 2), $r(2, 3, true), $r(3, 4, true), $r(4, 5, true))
);
Assert::equal(
    $inclusive->split(4, FloatRange::SPLIT_EXCLUSIVE_ENDS),
    $s($r(1, 2, false, true), $r(2, 3, false, true), $r(3, 4, false, true), $r(4, 5))
);
Assert::equal($empty->split(5), $s($empty));

// splitBy()
Assert::equal(
    $inclusive->splitBy([-10, 2, 4, 10]),
    $s($r(1, 2), $r(2, 4), $r(4, 5))
);
Assert::equal(
    $exclusive->splitBy([-10, 2, 4, 10]),
    $s($r(1, 2, true), $r(2, 4), $r(4, 5, false, true))
);
Assert::equal(
    $inclusive->splitBy([-10, 2, 4, 10], FloatRange::SPLIT_EXCLUSIVE_STARTS),
    $s($r(1, 2), $r(2, 4, true), $r(4, 5, true))
);
Assert::equal(
    $inclusive->splitBy([-10, 2, 4, 10], FloatRange::SPLIT_EXCLUSIVE_ENDS),
    $s($r(1, 2, false, true), $r(2, 4, false, true), $r(4, 5))
);

// envelope()
Assert::equal($inclusive->envelope($r(5, 6)), $r(1, 6));
Assert::equal($inclusive->envelope($r(0, 1)), $r(0, 5));
Assert::equal($inclusive->envelope($r(-10, -5)), $r(-10, 5));
Assert::equal($inclusive->envelope($r(-10, -5), $r(5, 10)), $r(-10, 10));
Assert::equal($inclusive->envelope($empty), $inclusive);

Assert::equal($exclusive->envelope($r(5, 6)), $r(1, 6, true));
Assert::equal($exclusive->envelope($r(0, 1)), $r(0, 5, false, true));
Assert::equal($exclusive->envelope($r(-10, -5)), $r(-10, 5, false, true));
Assert::equal($exclusive->envelope($r(-10, -5), $r(5, 10)), $r(-10, 10));
Assert::equal($exclusive->envelope($empty), $exclusive);

// intersect()
Assert::equal($inclusive->intersect($r(3, 6)), $r(3, 5));
Assert::equal($inclusive->intersect($r(0, 1)), $r(1, 1));
Assert::equal($inclusive->intersect($r(3, 6), $r(0, 4)), $r(3, 4));
Assert::equal($inclusive->intersect($r(-10, -5)), $empty);
Assert::equal($inclusive->intersect($r(-10, -5), $r(5, 10)), $empty);
Assert::equal($inclusive->intersect($empty), $empty);

Assert::equal($exclusive->intersect($r(3, 6)), $r(3, 5, false, true));
Assert::equal($exclusive->intersect($r(0, 3)), $r(1, 3, true, false));
Assert::equal($exclusive->intersect($r(0, 1)), $empty);
Assert::equal($exclusive->intersect($r(5, 10)), $empty);
Assert::equal($exclusive->intersect($r(3, 6), $r(0, 4)), $r(3, 4));
Assert::equal($exclusive->intersect($r(-10, -5)), $empty);
Assert::equal($exclusive->intersect($r(-10, -5), $r(5, 10)), $empty);
Assert::equal($exclusive->intersect($empty), $empty);

// union()
Assert::equal($inclusive->union($r(3, 6)), $s($r(1, 6)));
Assert::equal($inclusive->union($r(0, 1)), $s($r(0, 5)));
Assert::equal($inclusive->union($r(3, 6), $r(0, 4)), $s($r(0, 6)));
Assert::equal($inclusive->union($r(10, 20)), $s($inclusive, $r(10, 20)));
Assert::equal($inclusive->union($all), $s($all));
Assert::equal($inclusive->union($empty), $s($inclusive));

Assert::equal($exclusive->union($r(3, 6)), $s($r(1, 6, true)));
Assert::equal($exclusive->union($r(0, 3)), $s($r(0, 5, false, true)));
Assert::equal($exclusive->union($r(0, 1)), $s($r(0, 5, false, true)));
Assert::equal($exclusive->union($r(0, 1, false, true)), $s($r(0, 1, false, true), $exclusive));
Assert::equal($exclusive->union($r(5, 6)), $s($r(1, 6, true)));
Assert::equal($exclusive->union($r(5, 6, true, false)), $s($exclusive, $r(5, 6, true, false)));
Assert::equal($exclusive->union($r(3, 6), $r(0, 4)), $s($r(0, 6)));
Assert::equal($exclusive->union($r(10, 20)), $s($exclusive, $r(10, 20)));
Assert::equal($exclusive->union($all), $s($all));
Assert::equal($exclusive->union($empty), $s($exclusive));

// difference()
Assert::equal($inclusive->difference($r(3, 6)), $s($r(1, 3, false, true), $r(5, 6, true, false)));
Assert::equal($inclusive->difference($r(0, 1)), $s($r(0, 1, false, true), $r(1, 5, true, false)));
Assert::equal($inclusive->difference($r(3, 6), $r(0, 4)), $s($r(0, 1, false, true), $r(5, 6, true, false)));
Assert::equal($inclusive->difference($r(10, 20)), $s($inclusive, $r(10, 20)));
Assert::equal($inclusive->difference($all), $s(new FloatRange(FloatRange::MIN, 1.0, false, true), new FloatRange(5.0, FloatRange::MAX, true, false)));
Assert::equal($inclusive->difference($empty), $s($inclusive));

// subtract()
Assert::equal($inclusive->subtract($r(0, 2)), $s($r(2, 5, true, false)));
Assert::equal($inclusive->subtract($r(4, 6)), $s($r(1, 4, false, true)));
Assert::equal($inclusive->subtract($r(2, 3)), $s($r(1, 2, false, true), $r(3, 5, true, false)));
Assert::equal($inclusive->subtract($r(0, 1), $r(4, 6)), $s($r(1, 4, true, true)));
Assert::equal($inclusive->subtract($empty), $s($inclusive));
Assert::equal($inclusive->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s($empty));

// invert()
Assert::equal($inclusive->invert(), $s(
    new FloatRange(FloatRange::MIN, 1.0, false, true),
    new FloatRange(5.0, FloatRange::MAX, true, false)
));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s());

// countOverlaps()
Assert::equal(FloatRange::countOverlaps($empty), []);
Assert::equal(FloatRange::countOverlaps($inclusive, $r(0, 1)), [
    [$r(0, 1, false, true), 1],
    [$r(1, 1, false, false), 2],
    [$r(1, 5, true, false), 1],
]);
Assert::equal(FloatRange::countOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    [$r(0, 1, false, true), 1],
    [$r(1, 2, false, true), 2],
    [$r(2, 5, false, false), 3],
    [$r(5, 6, true, false), 2],
    [$r(6, 7, true, false), 1],
]);

// explodeOverlaps()
Assert::equal(FloatRange::explodeOverlaps($empty), []);
Assert::equal(FloatRange::explodeOverlaps($inclusive, $r(0, 1)), [
    $r(0, 1, false, true),
    $r(1, 1, false, false),
    $r(1, 1, false, false),
    $r(1, 5, true, false),
]);
Assert::equal(FloatRange::explodeOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    $r(0, 1, false, true),
    $r(1, 2, false, true),
    $r(1, 2, false, true),
    $r(2, 5, false, false),
    $r(2, 5, false, false),
    $r(2, 5, false, false),
    $r(5, 6, true, false),
    $r(5, 6, true, false),
    $r(6, 7, true, false),
]);
