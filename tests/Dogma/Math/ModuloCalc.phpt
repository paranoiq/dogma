<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Tests\Math;

use Dogma\InvalidArgumentException;
use Dogma\Math\ModuloCalc;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


// differences()
Assert::same(ModuloCalc::differences([0], 60), [60]);
Assert::same(ModuloCalc::differences([10], 60), [60]);
Assert::same(ModuloCalc::differences([0, 10, 20, 30, 40, 50], 60), [10, 10, 10, 10, 10, 10]);
Assert::same(ModuloCalc::differences([0, 20, 30, 50], 60), [20, 10, 20, 10]);
Assert::throws(function (): void {
    ModuloCalc::differences([], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::differences([0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::differences([-1, 1, 2], 60);
}, InvalidArgumentException::class);


// roundTo()
Assert::same(ModuloCalc::roundTo(3, [0, 10, 20, 30, 40, 50], 60), [0, false]);
Assert::same(ModuloCalc::roundTo(23, [0, 10, 20, 30, 40, 50], 60), [20, false]);
Assert::same(ModuloCalc::roundTo(27, [0, 10, 20, 30, 40, 50], 60), [30, false]);
Assert::same(ModuloCalc::roundTo(53, [0, 10, 20, 30, 40, 50], 60), [50, false]);
Assert::same(ModuloCalc::roundTo(57, [0, 10, 20, 30, 40, 50], 60), [0, true]);
Assert::throws(function (): void {
    ModuloCalc::roundTo(3, [], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::roundTo(3, [0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::roundTo(3, [-1, 1, 2], 60);
}, InvalidArgumentException::class);


// roundUpTo()
Assert::same(ModuloCalc::roundUpTo(3, [0, 10, 20, 30, 40, 50], 60), [10, false]);
Assert::same(ModuloCalc::roundUpTo(23, [0, 10, 20, 30, 40, 50], 60), [30, false]);
Assert::same(ModuloCalc::roundUpTo(27, [0, 10, 20, 30, 40, 50], 60), [30, false]);
Assert::same(ModuloCalc::roundUpTo(53, [0, 10, 20, 30, 40, 50], 60), [0, true]);
Assert::same(ModuloCalc::roundUpTo(57, [0, 10, 20, 30, 40, 50], 60), [0, true]);
Assert::throws(function (): void {
    ModuloCalc::roundUpTo(3, [], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::roundUpTo(3, [0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::roundUpTo(3, [-1, 1, 2], 60);
}, InvalidArgumentException::class);


// roundDownTo()
Assert::same(ModuloCalc::roundDownTo(3, [0, 10, 20, 30, 40, 50], 60), 0);
Assert::same(ModuloCalc::roundDownTo(23, [0, 10, 20, 30, 40, 50], 60), 20);
Assert::same(ModuloCalc::roundDownTo(27, [0, 10, 20, 30, 40, 50], 60), 20);
Assert::same(ModuloCalc::roundDownTo(53, [0, 10, 20, 30, 40, 50], 60), 50);
Assert::same(ModuloCalc::roundDownTo(57, [0, 10, 20, 30, 40, 50], 60), 50);
Assert::throws(function (): void {
    ModuloCalc::roundDownTo(3, [], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::roundDownTo(3, [0, 1, 60], 60);
}, InvalidArgumentException::class);
Assert::throws(function (): void {
    ModuloCalc::roundDownTo(3, [-1, 1, 2], 60);
}, InvalidArgumentException::class);
