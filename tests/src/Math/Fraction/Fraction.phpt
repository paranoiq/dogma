<?php declare(strict_types = 1);

namespace Dogma\Tests\Math;

use Dogma\Math\Fraction;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// simplify()
Assert::equal((new Fraction(15, 60))->simplify(), new Fraction(1, 4));
Assert::equal((new Fraction(84, 140))->simplify(), new Fraction(3, 5));
