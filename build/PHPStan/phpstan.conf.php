<?php declare(strict_types = 1);

$ignore = [];
if (PHP_VERSION_ID < 80000) {
    // 7.4
    $ignore[] = '~Parameter #1 \$argument of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~'; // you know nothing
    $ignore[] = '~Parameter #1 \$items of class Dogma\\\\ImmutableArray constructor expects array, array\|false given.~'; // in ImmutableArray
    $ignore[] = '~Method Dogma\\\\Arr::combine\\(\\) should return array<T> but returns array<T, T>\\|false~'; // handled on input
    $ignore[] = '~has unknown class Curl(Multi)?Handle as its type.~'; # PHP 7 -> 8
    $ignore[] = '~has invalid return type Curl(Multi)?Handle~'; # PHP 7 -> 8
}
if (PHP_VERSION_ID >= 80000) {
    // 8.0+
    $ignore[] = '~Strict comparison using === between CurlMultiHandle and false will always evaluate to false.~'; // in HttpChannelManager; probably a reflection bug
}
if (PHP_VERSION_ID >= 80500) {
    // 8.5+
    $ignore[] = '~function chr expects int<0, 255>, int<101, 356> given~'; // chr()
}

$excludePaths = [
    '*/tests/*/data/*',
];

return [
    'parameters' => [
        'ignoreErrors' => $ignore,
        'excludePaths' => [
            'analyse' => $excludePaths,
        ],
    ],
];
