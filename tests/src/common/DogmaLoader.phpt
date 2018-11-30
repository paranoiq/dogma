<?php declare(strict_types = 1);

namespace Dogma\Tests\DogmaLoader;

use Dogma\DogmaLoader;
use Dogma\Tester\Assert;
use Dogma\Type;

require_once __DIR__ . '/../bootstrap.php';

$loader = DogmaLoader::getInstance();
$classMap = $loader->getClassMap();

Assert::same(
    $classMap[DogmaLoader::class],
    sprintf(
        '%s%ssrc%scommon%sDogmaLoader.php',
        dirname(dirname(dirname(__DIR__))),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);

Assert::same(
    $classMap[Type::class],
    sprintf(
        '%s%ssrc%scommon%stypes%sType.php',
        dirname(dirname(dirname(__DIR__))),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);
