<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tools\Ansi;

use Dogma\StrictBehaviorMixin;

class TableFormatter
{
    use StrictBehaviorMixin;

    /** @var int */
    private $screenWidth;

    public function __construct(int $screenWidth)
    {
        $this->screenWidth = $screenWidth;
    }

    /**
     * @param iterable|string[][] $source
     */
    public function render(iterable $source): void
    {
        $formats = [];
        $columns = [];
        $rows = [];
        foreach ($source as $j => $row) {
            if ($j === 0) {
                foreach ($row as $column => $value) {
                    $columns[] = $column;
                }
                $rows[] = $columns;
            }
            $values = [];
            $i = 0;
            /** @var mixed $value */
            foreach ($row as $value) {
                if (is_numeric($value)) {
                    $values[] = $value;
                    if (!isset($formats[$i])) {
                        $formats[$i] = 'number';
                    }
                } elseif ($value === null) {
                    $values[] = 'NULL';
                } elseif ($value instanceof \DateTimeInterface) {
                    if ($value->format('His') === '000000') {
                        $formats[$i] = 'date';
                        $values[] = $value->format('Y-m-d');
                    } else {
                        $formats[$i] = 'datetime';
                        $values[] = $value->format('Y-m-d H:i:s');
                    }
                } elseif (preg_match('/\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2}/', $value)) {
                    $values[] = $value;
                    $formats[$i] = 'datetime';
                } elseif (preg_match('/\\d{4}-\\d{2}-\\d{2}/', $value)) {
                    $values[] = $value;
                    $formats[$i] = 'date';
                } elseif (preg_match('/\\d{2}:\\d{2}:\\d{2}/', $value)) {
                    $values[] = $value;
                    $formats[$i] = 'time';
                } else {
                    $values[] = trim(str_replace("\r", '', $value));
                    $formats[$i] = 'string';
                }
                $i++;
            }
            $rows[] = $values;
        }

        for ($n = 0; $n < count($columns); $n++) {
            if (!isset($formats[$n])) {
                $formats[$n] = 'string';
            }
        }

        $padding = ($this->screenWidth / count($columns) < 5) ? 0 : 2;
        $availableWidth = $this->screenWidth - count($columns) * ($padding + 1) - 2;
        $columnWidths = $this->calculateColumnWidths($rows, $availableWidth, count($columns), $formats);

        $this->renderDivider($columnWidths, $padding);
        foreach ($rows as $key => $row) {
            $this->renderRow($row, $columnWidths, $formats, $padding);
            if ($key === 0) {
                $this->renderDivider($columnWidths, $padding);
            }
        }
        $this->renderDivider($columnWidths, $padding);
    }

    private function renderDivider(array $columnWidths, int $padding): void
    {
        $row = '+';
        foreach ($columnWidths as $i => $columnWidth) {
            if ($i !== 0) {
                $row .= '+';
            }
            $row .= str_repeat('-', $columnWidth + $padding);
        }
        echo $this->formatLayout($row . "+\n");
    }

    private function renderRow(array $row, array $columnWidths, array $formats, int $padding): void
    {
        echo $this->formatLayout($padding ? '| ' : '|');
        $remainders = [];
        foreach ($row as $i => $value) {
            if ($i !== 0) {
                echo $this->formatLayout($padding ? ' | ' : '|');
            }
            $columnWidth = $columnWidths[$i];

            $length = mb_strlen($value);
            if ($value === 'NULL') {
                echo $this->formatValue(C::cyan(substr($value, 0, $columnWidth)), $formats[$i], str_repeat(' ', max($columnWidth - 4, 0)));
                $remainders[$i] = '';
            } elseif ($length <= $columnWidth) {
                echo $this->formatValue($value, $formats[$i], str_repeat(' ', $columnWidth - $length));
                $remainders[$i] = '';
            } else {
                $wrapPosition = $this->getWordWrapPosition($value, $columnWidth);
                $words = mb_substr($value, 0, $wrapPosition);
                $remainders[$i] = trim(mb_substr($value, $wrapPosition));
                echo $this->formatValue($words, $formats[$i], str_repeat(' ', $columnWidth - $wrapPosition));
            }
        }
        echo $this->formatLayout($padding ? " |\n" : "|\n");
        if (array_filter($remainders)) {
            $this->renderRow($remainders, $columnWidths, $formats, $padding);
        }
    }

    private function formatLayout(string $layout): string
    {
        return C::gray($layout);
    }

    private function formatValue(string $value, string $format, string $padding): string
    {
        if ($format === 'string') {
            // escape new lines and tabulators
            $value = str_replace(["\n", "\t"], [C::cyan('↓'), C::cyan('→')], $value);
            // highlight html markup
            $value = preg_replace('/(<[^>]+(\\>|$)|^[^<]+>)/', C::gray('\\0'), $value);
            // highlight html entities
            return preg_replace('/&[a-z]+;/', C::gray('\\0'), $value) . $padding;
        } elseif ($format === 'number') {
            return $padding . $value;
        } else {
            return $value . $padding;
        }
    }

    private function getWordWrapPosition(string $value, int $length): int
    {
        $pos = strrpos(mb_substr($value, 0, $length), ' ');
        if ($pos) {
            $pos = mb_strlen(substr($value, 0, $pos));
        }
        // break words if row is less than 70% full
        if ($pos < $length * 0.70) {
            $pos = $length;
        }

        return $pos ?: $length;
    }

    /**
     * @param string[][] $rows
     * @param int $tableWidth
     * @param int $columnsCount
     * @param string[] $formats
     * @return int[]
     */
    private function calculateColumnWidths(array $rows, int $tableWidth, int $columnsCount, array $formats): array
    {
        if ($columnsCount > $tableWidth) {
            throw new \RuntimeException('Too much columns. Cannot create a table layout.');
        }

        $zeroes = array_fill(0, $columnsCount, 0);

        $headLengths = $zeroes;
        $maxLengths = $zeroes;
        $maxWordLengths = $zeroes;
        $flexible = [];
        $wrapable = [];
        $columnWidths = [];

        foreach ($rows as $j => $row) {
            for ($i = 0; $i < $columnsCount; $i++) {
                $cell = $row[$i];
                $length = mb_strlen($cell);

                if ($j === 0) {
                    $headLengths[$i] = $length;
                } else {
                    $maxLengths[$i] = max($maxLengths[$i], $length);
                }

                if (strpos($cell, ' ') !== false) {
                    $wrapable[$i] = true;
                    $maxWordLengths[$i] = max($maxWordLengths[$i], $this->getMaxWordLength($cell));
                } else {
                    $wrapable[$i] = false;
                    $maxWordLengths[$i] = $maxLengths[$i];
                }
            }
        }

        $left = $tableWidth;
        $avg = $left / $columnsCount;

        // determine whether columns should be flexible and assign width of non-flexible columns
        foreach ($maxLengths as $i => $maxLength) {
            $flexible[$i] = ($maxLength > 2 * $avg);
            if (!$flexible[$i]) {
                $columnWidths[$i] = max($maxLength, $headLengths[$i]);
                $left -= $columnWidths[$i];
            }
        }

        // wrap all wrapable columns
        if (array_sum($maxLengths) > $tableWidth) {
            foreach ($maxWordLengths as $i => $maxWordLength) {
                if ($wrapable[$i] && !$flexible[$i]) {
                    $left += $columnWidths[$i] - $maxWordLength;
                    $columnWidths[$i] = $maxWordLength;
                }
            }
        }

        // wrap headers
        if (array_sum($maxLengths) > array_sum($columnWidths)) {
            foreach ($maxLengths as $i => $maxLength) {
                if (!$wrapable[$i] && !$flexible[$i] && $maxLength < $headLengths[$i]) {
                    $left += $columnWidths[$i] - $maxLength;
                    $columnWidths[$i] = $maxLength;
                }
            }
        }

        // calculate weights for flexible columns. the max width is capped at triple of page width
        $totalWidth = 0;
        for ($i = 0; $i < $columnsCount; $i++) {
            if ($flexible[$i]) {
                $maxLengths[$i] = min($maxLengths[$i], $tableWidth * 3);
                $totalWidth += $maxLengths[$i];
            }
        }

        // assign width for flexible columns
        foreach ($maxLengths as $i => $maxLength) {
            if ($flexible[$i]) {
                $columnWidths[$i] = (int) round($left * $maxLength / $totalWidth);
            }
        }

        // tweak widths of datetime columns
        $dateTimeColumnWidths = [];
        $nonDateTimeColumnWidths = [];
        foreach ($columnWidths as $i => $width) {
            if ($formats[$i] === 'date' || $formats[$i] === 'datetime' || $formats[$i] === 'time') {
                $dateTimeColumnWidths[$i] = $width;
            } else {
                $nonDateTimeColumnWidths[$i] = $width;
            }
        }
        foreach ($dateTimeColumnWidths as $i => $width) {
            if (($formats[$i] === 'datetime' || $formats[$i] === 'date') && ($width === 8 || $width === 9)) {
                $columnWidths[$i] = 10;
                $columnWidths[array_search(max($nonDateTimeColumnWidths), $nonDateTimeColumnWidths, true)] -= (10 - $width);
            } elseif (($formats[$i] === 'datetime' || $formats[$i] === 'date') && $width === 6) {
                $columnWidths[$i] = 7;
                $columnWidths[array_search(max($nonDateTimeColumnWidths), $nonDateTimeColumnWidths, true)]--;
            } elseif ($formats[$i] === 'time' && ($width === 6 || $width === 7)) {
                $columnWidths[$i] = 8;
                $columnWidths[array_search(max($nonDateTimeColumnWidths), $nonDateTimeColumnWidths, true)] -= (8 - $width);
            }
        }

        // fix columns with zero width on extreme conditions
        foreach ($columnWidths as $i => $width) {
            if ($width === 0) {
                $columnWidths[$i] = 1;
                $columnWidths[array_search(max($columnWidths), $columnWidths, true)]--;
            }
        }

        // fix too wide table due to rounding errors
        while (array_sum($columnWidths) > $tableWidth) {
            $columnWidths[array_search(max($columnWidths), $columnWidths, true)]--;
        }

        ksort($columnWidths);

        return $columnWidths;
    }

    private function getMaxWordLength(string $string): int
    {
        $words = preg_split('/ /', $string);

        return min(max(array_map('mb_strlen', $words)), 30);
    }

}
