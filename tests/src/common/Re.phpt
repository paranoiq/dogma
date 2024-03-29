<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use Dogma\Re;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


pos:
Assert::same(Re::pos('abc@def', '~#~'), null);
Assert::same(Re::pos('abc@def', '~@~'), 3);
Assert::same(Re::pos('abc@def', '~@([def])~'), 3);

submatch:
Assert::same(Re::submatch('abc@def', '~(abc)~'), 'abc');
Assert::same(Re::submatch('abc@def', '~(def)~'), 'def');
Assert::same(Re::submatch('abc@def', '~(foo)~'), null);
