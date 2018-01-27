<?php
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Time\Format;

use Dogma\Time\DateTimeFormatter;

class VariableNode implements \Dogma\Time\Format\Node
{
    use \Dogma\StrictBehaviorMixin;

    /** @var string */
    private $format;

    /** @var string */
    private $group;

    /** @var string */
    private $modifiers;

    /** @var string */
    private $value;

    /** @var string */
    private $formatted;

    public function __construct(string $variable, string $format, string $modifiers)
    {
        $this->format = $format;
        $this->group = self::$characterGroups[$format];
        $this->modifiers = $modifiers;
    }

    public function fillValue(DateTimeValues $values): void
    {
        switch ($this->group) {
            case DateTimeFormatter::YEAR:
                if ($values->year === null) {
                    $values->year = (int) $values->dateTime->format('Y');
                }
                $this->value = $this->formatted;
                $this->formatted = (string) $values->year;
                break;
            case DateTimeFormatter::YEAR_SHORT:
                if ($values->year === null) {
                    $values->year = (int) $values->dateTime->format('Y');
                }
                $this->value = $values->year;
                $this->formatted = substr((string) $values->year, -2);
                break;
            case DateTimeFormatter::LEAP_YEAR:
                if ($values->year === null) {
                    $values->year = (int) $values->dateTime->format('Y');
                }
                $this->value = $values->year;
                $this->formatted = $values->dateTime->format('L');
                break;
            case DateTimeFormatter::DAY_OF_YEAR:
                if ($values->dayOfYear === null) {
                    $values->dayOfYear = (int) $values->dateTime->format('z');
                }
                $this->value = $values->dayOfYear;
                $this->formatted = (string) ($values->dayOfYear + 1);
                break;
            case DateTimeFormatter::DAY_OF_YEAR_INDEX:
                if ($values->dayOfYear === null) {
                    $values->dayOfYear = (int) $values->dateTime->format('z');
                }
                $this->value = $values->dayOfYear;
                $this->formatted = (string) $values->dayOfYear;
                break;
            case DateTimeFormatter::QUARTER:
                if ($values->quarter === null) {
                    $values->quarter = (int) ($values->dateTime->format('n') / 3);
                }
                $this->value = $values->quarter;
                $this->formatted = (string) $values->quarter;
                break;
            case DateTimeFormatter::MONTH_LZ:
                if ($values->month === null) {
                    $values->month = (int) $values->dateTime->format('n');
                }
                $this->value = $values->month;
                $this->formatted = $values->dateTime->format('m');
                break;
            case DateTimeFormatter::MONTH:
                if ($values->month === null) {
                    $values->month = (int) $values->dateTime->format('n');
                }
                $this->value = $values->month;
                $this->formatted = (string) $values->month;
                break;
            case DateTimeFormatter::MONTH_NAME:
                if ($values->month === null) {
                    $values->month = (int) $values->dateTime->format('n');
                }
                $this->value = $values->month;
                $this->formatted = $values->format('F');
                break;
            case DateTimeFormatter::MONTH_NAME_SHORT:
                if ($values->month === null) {
                    $values->month = (int) $values->dateTime->format('n');
                }
                $this->value = $values->month;
                $this->formatted = $values->format('M');
                break;
        }
    }

}
