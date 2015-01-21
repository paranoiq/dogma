<?php

namespace Dogma\Tests\DogmaLoader;

use Dogma\DogmaLoader;
use Dogma\Type;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$loader = DogmaLoader::getInstance();
$classMap = $loader->getClassMap();

Assert::same(
    $classMap[DogmaLoader::class],
    sprintf(
        '%s%sDogma%scommon%sDogmaLoader.php',
        dirname(dirname(dirname(__DIR__))),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);

Assert::same(
    $classMap[Type::class],
    sprintf(
        '%s%sDogma%scommon%stypes%sType.php',
        dirname(dirname(dirname(__DIR__))),
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR,
        DIRECTORY_SEPARATOR
    )
);
