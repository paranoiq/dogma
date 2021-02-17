<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Math\Matrix;

use Dogma\Arr;
use Dogma\ShouldNotHappenException;
use Dogma\StaticClassMixin;
use function array_keys;
use function array_values;
use const PHP_INT_MAX;

class MatrixCalc
{
    use StaticClassMixin;

    /**
     * @param mixed[][] $matrix
     * @param mixed $value
     * @return mixed[][]
     */
    public static function fillHoles(array $matrix, $value): array
    {
        $minJ = PHP_INT_MAX;
        $maxJ = 0;
        foreach ($matrix as $row) {
            $minJ = min($minJ, min(array_keys($row)));
            $maxJ = max($maxJ, max(array_keys($row)));
        }
        if ($minJ === PHP_INT_MAX) {
            throw new ShouldNotHappenException('This matrix stinks!');
        }

        foreach ($matrix as $i => $row) {
            for ($j = $minJ; $j <= $maxJ; $j++) {
                if (!isset($matrix[$i][$j])) {
                    $matrix[$i][$j] = $value;
                }
            }
        }

        return $matrix;
    }

    /**
     * @param mixed[][] $matrix
     * @param mixed $emptyValue
     * @return mixed[][]
     */
    public static function dropEmptyColumns(array $matrix, $emptyValue = 0): array
    {
        $matrix = Arr::transpose($matrix);
        foreach ($matrix as $i => $column) {
            foreach ($column as $value) {
                if ($value !== $emptyValue) {
                    continue 2;
                }
            }
            unset($matrix[$i]);
        }

        return Arr::transpose(array_values($matrix));
    }

}
