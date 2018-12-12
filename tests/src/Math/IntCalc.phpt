<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tests\Math;

use Dogma\Math\IntCalc;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

// roundTo()
Assert::same(IntCalc::roundTo(0, 3), 0);
Assert::same(IntCalc::roundTo(20, 3), 21);
Assert::same(IntCalc::roundTo(22, 3), 21);
Assert::same(IntCalc::roundTo(-20, 3), -21);
Assert::same(IntCalc::roundTo(-22, 3), -21);
Assert::same(IntCalc::roundTo(20, -3), 21);
Assert::same(IntCalc::roundTo(22, -3), 21);

// roundUpTo()
Assert::same(IntCalc::roundUpTo(0, 3), 0);
Assert::same(IntCalc::roundUpTo(20, 3), 21);
Assert::same(IntCalc::roundUpTo(22, 3), 24);
Assert::same(IntCalc::roundUpTo(-20, 3), -18);
Assert::same(IntCalc::roundUpTo(-22, 3), -21);
Assert::same(IntCalc::roundUpTo(20, -3), 21);
Assert::same(IntCalc::roundUpTo(22, -3), 24);

// roundDownTo()
Assert::same(IntCalc::roundDownTo(0, 3), 0);
Assert::same(IntCalc::roundDownTo(20, 3), 18);
Assert::same(IntCalc::roundDownTo(22, 3), 21);
Assert::same(IntCalc::roundDownTo(-20, 3), -21);
Assert::same(IntCalc::roundDownTo(-22, 3), -24);
Assert::same(IntCalc::roundDownTo(20, -3), 18);
Assert::same(IntCalc::roundDownTo(22, -3), 21);
