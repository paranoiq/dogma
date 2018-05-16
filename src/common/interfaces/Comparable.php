<?php declare(strict_types = 1);

namespace Dogma;

interface Comparable
{

    /**
     * @param \Dogma\Comparable $other
     * @return int @see \Dogma\ComparisonResult
     */
    public function compare(self $other): int;

}
