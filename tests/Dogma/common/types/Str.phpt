<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use Dogma\Str;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


// toFirst()
Assert::same(Str::toFirst('abc@def', '@'), 'abc');
Assert::same(Str::toFirst('abc@def', '#'), 'abc@def');

// fromFirst()
Assert::same(Str::fromFirst('abc@def', '@'), 'def');
Assert::same(Str::fromFirst('abc@def', '#'), '');

// splitByFirst()
Assert::same(Str::splitByFirst('abc@def', '@'), ['abc', 'def']);
Assert::same(Str::splitByFirst('abc@def@ghi', '@'), ['abc', 'def@ghi']);
