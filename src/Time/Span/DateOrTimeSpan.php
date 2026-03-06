<?php declare(strict_types = 1);

namespace Dogma\Time\Span;

use DateInterval;
use Dogma\Comparable;
use Dogma\Equalable;

interface DateOrTimeSpan extends Equalable, Comparable
{

    public function toNative(): DateInterval;

    /**
     * @return DateInterval[]
     */
    public function toPositiveAndNegative(): array;

    public function format(string $format = '', ?DateTimeSpanFormatter $formatter = null): string;

    public function isZero(): bool;

    public function isMixed(): bool;

    //public function add(self ...$other): static;

    //public function subtract(self ...$other): static;

    public function invert(): static;

    public function abs(): static;

    /**
     * Normalizes values by summarizing smaller units into bigger. eg: '34 days' -> '1 month, 4 days'
     */
    public function normalize(): static;

    /**
     * @return array<int>
     */
    public function getValues(): array;

}
