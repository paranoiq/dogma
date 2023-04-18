<?php declare(strict_types = 1);

$ignore = [];
// 7.4
if (PHP_VERSION_ID < 80000) {
    $ignore[] = '~Parameter #1 \$argument of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~'; // you know nothing
    $ignore[] = '~Parameter #1 \$items of class Dogma\\\\ImmutableArray constructor expects array, array\|false given.~'; // in ImmutableArray
    $ignore[] = '~Method Dogma\\\\Arr::combine\\(\\) should return array<T> but returns array<T, T>\\|false~'; // handled on input
    $ignore[] = '~has unknown class Curl(Multi)?Handle as its type.~'; # PHP 7 -> 8
    $ignore[] = '~has invalid return type Curl(Multi)?Handle~'; # PHP 7 -> 8
}
// 8.0+
if (PHP_VERSION_ID >= 80000) {
    $ignore[] = '~Parameter #1 \$objectOrClass of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~'; // in MethodTypeParser; temporary
    $ignore[] = '~Strict comparison using === between CurlMultiHandle and false will always evaluate to false.~'; // in HttpChannelManager; probably a reflection bug
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
