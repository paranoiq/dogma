<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Interval;

use Dogma\Arr;
use function array_values;

trait IntervalSetNormalizeMixin
{

    /**
     * @deprecated Unnecessary anymore, intervals always normalized.
     */
    public function normalize(): self
    {
        return $this;
    }

    /**
     * Join overlapping intervals.
     *
     * @param T[] $intervals
     * @return T[]
     *
     * @template T of Interval
     */
    private static function normalizeIntervals(array $intervals): array
    {
        $intervals = array_values($intervals);
        foreach ($intervals as $i => $interval) {
            if ($interval->isEmpty()) {
                unset($intervals[$i]);
            }
        }

        $intervals = Arr::sortComparableValues($intervals);
        $count = count($intervals) - 1;
        for ($n = 0; $n < $count; $n++) {
            if ($intervals[$n]->intersects($intervals[$n + 1]) || $intervals[$n]->touches($intervals[$n + 1])) {
                $intervals[$n + 1] = $intervals[$n]->envelope($intervals[$n + 1]);
                unset($intervals[$n]);
            }
        }

        return array_values($intervals);
    }

}
