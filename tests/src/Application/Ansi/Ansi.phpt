<?php declare(strict_types = 1);

namespace Dogma\Tests\Str;

use Dogma\Application\Ansi\Ansi;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


$ansi = new Ansi();

Assert::same($ansi->process(' foo '), ' foo ');
Assert::same($ansi->process(' {{foo}} '), ' {{foo}} ');

