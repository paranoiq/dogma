<?php declare(strict_types=1);

$ignore = PHP_VERSION_ID < 80000
    ? [
        '~Parameter #1 \$argument of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~', # you know nothing
        '~Method Dogma\\\\Arr::combine\(\) should return array but returns array\|false.~', # in Arr
        '~Parameter #1 \$items of class Dogma\\\\ImmutableArray constructor expects array, array\|false given.~', # in ImmutableArray
        '~has unknown class Curl(Multi)?Handle as its type.~', # PHP 7 -> 8
        '~has invalid type Curl(Multi)?Handle.~', # PHP 7 -> 8
        '~Method Dogma\\\\Obj::dumpHash\(\) has parameter \$object with no typehint specified~',
    ]
    : [
        '~Parameter #1 \$objectOrClass of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~', # in MethodTypeParser; temporary
        '~Strict comparison using === between CurlMultiHandle and false will always evaluate to false.~', # in HttpChannelManager; probably a reflection bug
        '~Method Dogma\\\\Obj::dumpHash\(\) has parameter \$object with no typehint specified~',
    ];

return ['parameters' => ['ignoreErrors' => $ignore]];
