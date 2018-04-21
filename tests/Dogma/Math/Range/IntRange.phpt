<?php

namespace Dogma\Tests\Math\Decimal;

use Dogma\Math\Range\IntRange;
use Dogma\Math\Range\IntRangeSet;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

$range = new IntRange(1, 5);
$empty = IntRange::createEmpty();
$all = IntRange::createAll();

$r = function (int $start, int $end) {
    return new IntRange($start, $end);
};
$s = function (IntRange ...$items) {
    return new IntRangeSet($items);
};

// shift()
Assert::equal($range->shift(10), $r(11, 15));

// multiply()
Assert::equal($range->multiply(10), $r(10, 50));

// getStart()
Assert::same($range->getStart(), 1);

// getEnd()
Assert::same($range->getEnd(), 5);

// isEmpty()
Assert::false($range->isEmpty());
Assert::false((new IntRange(1, 1))->isEmpty());
Assert::false($all->isEmpty());
Assert::true($empty->isEmpty());

// equals()
Assert::true($range->equals($r(1, 5)));
Assert::false($range->equals($r(1, 4)));
Assert::false($range->equals($r(2, 5)));

// containsValue()
Assert::true($range->containsValue(1));
Assert::true($range->containsValue(3));
Assert::true($range->containsValue(5));
Assert::false($range->containsValue(0));
Assert::false($range->containsValue(6));

// contains()
Assert::true($range->contains($r(1, 5)));
Assert::true($range->contains($r(1, 3)));
Assert::true($range->contains($r(3, 5)));
Assert::false($range->contains($r(0, 5)));
Assert::false($range->contains($r(1, 6)));
Assert::false($range->contains($r(-1, 0)));
Assert::false($range->contains($empty));

// intersects()
Assert::true($range->intersects($r(1, 5)));
Assert::true($range->intersects($r(0, 1)));
Assert::true($range->intersects($r(5, 6)));
Assert::false($range->intersects($r(-1, 0)));
Assert::false($range->intersects($empty));

// touches()
Assert::true($range->touches($r(-10, 0)));
Assert::true($range->touches($r(6, 10)));
Assert::false($range->touches($r(-10, 1)));
Assert::false($range->touches($r(5, 10)));

// split()
Assert::equal($range->split(1), $s($range));
Assert::equal($range->split(2), $s($r(1, 3), $r(4, 5)));
Assert::equal($range->split(3), $s($r(1, 2), $r(3, 3), $r(4, 5)));
Assert::equal($range->split(4), $s($r(1, 1), $r(2, 3), $r(4, 4), $r(5, 5)));
Assert::equal($range->split(5), $s($r(1, 1), $r(2, 2), $r(3, 3), $r(4, 4), $r(5, 5)));
Assert::equal($range->split(9), $s($r(1, 1), $r(2, 2), $r(3, 3), $r(4, 4), $r(5, 5)));
Assert::equal($empty->split(5), $s($empty));

// splitBy()
Assert::equal($range->splitBy([-10, 2, 4, 10]), $s($r(1, 1), $r(2, 3), $r(4, 5)));

// envelope()
Assert::equal($range->envelope($r(5, 6)), $r(1, 6));
Assert::equal($range->envelope($r(0, 1)), $r(0, 5));
Assert::equal($range->envelope($r(-10, -5)), $r(-10, 5));
Assert::equal($range->envelope($r(-10, -5), $r(5, 10)), $r(-10, 10));
Assert::equal($range->envelope($empty), $range);

// intersect()
Assert::equal($range->intersect($r(3, 6)), $r(3, 5));
Assert::equal($range->intersect($r(0, 1)), $r(1, 1));
Assert::equal($range->intersect($r(3, 6), $r(0, 4)), $r(3, 4));
Assert::equal($range->intersect($r(-10, -5)), $empty);
Assert::equal($range->intersect($r(-10, -5), $r(5, 10)), $empty);
Assert::equal($range->intersect($empty), $empty);

// union()
Assert::equal($range->union($r(3, 6)), $s($r(1, 6)));
Assert::equal($range->union($r(0, 1)), $s($r(0, 5)));
Assert::equal($range->union($r(3, 6), $r(0, 4)), $s($r(0, 6)));
Assert::equal($range->union($r(10, 20)), $s($range, $r(10, 20)));
Assert::equal($range->union($all), $s($all));
Assert::equal($range->union($empty), $s($range));

// difference()
Assert::equal($range->difference($r(3, 6)), $s($r(1, 2), $r(6, 6)));
Assert::equal($range->difference($r(0, 1)), $s($r(0, 0), $r(2, 5)));
Assert::equal($range->difference($r(3, 6), $r(0, 4)), $s($r(0, 0), $r(6, 6)));
Assert::equal($range->difference($r(10, 20)), $s($range, $r(10, 20)));
Assert::equal($range->difference($all), $s($r(IntRange::MIN, 0), $r(6, IntRange::MAX)));
Assert::equal($range->difference($empty), $s($range));

// subtract()
Assert::equal($range->subtract($r(0, 2)), $s($r(3, 5)));
Assert::equal($range->subtract($r(4, 6)), $s($r(1, 3)));
Assert::equal($range->subtract($r(2, 3)), $s($r(1, 1), $r(4, 5)));
Assert::equal($range->subtract($r(0, 1), $r(4, 6)), $s($r(2, 3)));
Assert::equal($range->subtract($empty), $s($range));
Assert::equal($range->subtract($all), $s());
Assert::equal($all->subtract($empty), $s($all));
Assert::equal($empty->subtract($empty), $s($empty));

// invert()
Assert::equal($range->invert(), $s($r(IntRange::MIN, 0), $r(6, IntRange::MAX)));
Assert::equal($empty->invert(), $s($all));
Assert::equal($all->invert(), $s());

// countOverlaps()
Assert::equal(IntRange::countOverlaps($empty), []);
Assert::equal(IntRange::countOverlaps($range, $r(0, 1)), [
    [$r(0, 0), 1],
    [$r(1, 1), 2],
    [$r(2, 5), 1],
]);
Assert::equal(IntRange::countOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    [$r(0, 0), 1],
    [$r(1, 1), 2],
    [$r(2, 5), 3],
    [$r(6, 6), 2],
    [$r(7, 7), 1],
]);

// explodeOverlaps()
Assert::equal(IntRange::explodeOverlaps($empty), []);
Assert::equal(IntRange::explodeOverlaps($range, $r(0, 1)), [
    $r(0, 0),
    $r(1, 1),
    $r(1, 1),
    $r(2, 5),
]);
Assert::equal(IntRange::explodeOverlaps($r(0, 5), $r(1, 6), $r(2, 7)), [
    $r(0, 0),
    $r(1, 1),
    $r(1, 1),
    $r(2, 5),
    $r(2, 5),
    $r(2, 5),
    $r(6, 6),
    $r(6, 6),
    $r(7, 7),
]);
