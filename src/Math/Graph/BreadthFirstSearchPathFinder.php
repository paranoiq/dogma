<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Graph;

use Dogma\StrictBehaviorMixin;
use function array_reverse;
use function array_shift;
use function count;

/**
 * Initial cost O(1). Path cost max O(m+n) where m,n are edges and vertices
 */
class BreadthFirstSearchPathFinder
{
    use StrictBehaviorMixin;

    /** @var array<array{int|string, int|string}> */
    private array $edges;

    /**
     * @param array<array{int|string, int|string}> $edges
     */
    public function __construct(array $edges)
    {
        $this->edges = $edges;
    }

    public function getDistance(int|string $i, int|string $j): ?int
    {
        $path = $this->getPath($i, $j);

        return $path === null ? null : count($path) - 1;
    }

    /**
     * @return array<int|string>|null
     */
    public function getPath(int|string $i, int|string $j): ?array
    {
        if ($i === $j) {
            return [$i];
        }

        $visited = [$i => true];
        $queue = [$i];
        $predecessors = [];
        while ($queue !== []) {
            $current = array_shift($queue);
            foreach ($this->edges[$current] as $next) {
                if ($next === $j) {
                    $path = [$j, $current];
                    $previous = $current;
                    while ($previous !== $i) {
                        $path[] = $previous = $predecessors[$previous];
                    }

                    return array_reverse($path);
                }
                if (isset($visited[$next])) {
                    continue;
                }
                $predecessors[$next] = $current;
                $visited[$next] = true;
                $queue[] = $next;
            }
        }

        return null;
    }

}
