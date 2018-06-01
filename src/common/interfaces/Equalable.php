<?php declare(strict_types = 1);

namespace Dogma;

interface Equalable
{

    public function equals(Equalable $other): bool;

}
