<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Range;

use Dogma\Check;
use Dogma\StrictBehaviorMixin;

class IntRangeSet implements RangeSet
{
    use StrictBehaviorMixin;

    /** @var \Dogma\Math\Range\IntRange[] */
    private $ranges;

    /**
     * @param \Dogma\Math\Range\IntRange[] $ranges
     */
    public function __construct(array $ranges)
    {
        Check::itemsOfType($ranges, IntRange::class);

        $this->ranges = $ranges;
    }

    /**
     * @return \Dogma\Math\Range\IntRange[]
     */
    public function getRanges(): array
    {
        return $this->ranges;
    }

    public function isEmpty(): bool
    {
        return $this->ranges === [];
    }

    public function containsValue(int $value): bool
    {
        foreach ($this->ranges as $range) {
            if ($range->containsValue($value)) {
                return true;
            }
        }

        return false;
    }

    public function envelope(): IntRange
    {
        if ($this->ranges === []) {
            return IntRange::createEmpty();
        } else {
            return end($this->ranges)->envelope(...$this->ranges);
        }
    }

}
