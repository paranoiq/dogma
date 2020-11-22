<?php declare(strict_types=1);

$ignore = PHP_VERSION_ID < 80000
    ? [
        '~Parameter #1 \$argument of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~', # you know nothing
        '~Method Dogma\\\\Arr::combine\(\) should return array but returns array\|false.~', # in Arr
        '~Parameter #1 \$items of class Dogma\\\\ImmutableArray constructor expects array, array\|false given.~', # in ImmutableArray
        '~Strict comparison using === between array<string, class-string> and false will always evaluate to false.~', # in Cls
        '~Strict comparison using === between DateInterval and false will always evaluate to false.~', # in Time
        '~Strict comparison using === between static\(Dogma\\\\Time\\\\DateTime\) and false will always evaluate to false.~', # in DateTime
        '~has unknown class Curl(Multi)?Handle as its type.~', # PHP 7 -> 8
        '~has invalid type Curl(Multi)?Handle.~', # PHP 7 -> 8
        [
            'message' => '~Strict comparison using === between int and false will always evaluate to false.~',
            'path' => '../../src/Time/DateTime.php',
        ],
        [
            'message' => '~Strict comparison using === between string and false will always evaluate to false.~',
            'path' => '../../src/Language/Locale/Locale.php',
        ],
        [
            'message' => '~Strict comparison using === between (string|int) and false will always evaluate to false.~',
            'path' => '../../src/Language/Collator.php',
        ],
        [
            'message' => '~Strict comparison using === between resource and false will always evaluate to false.~',
            'path' => '../../src/Http/HttpRequest.php',
        ],
        [
            'message' => '~Strict comparison using === between (PDOStatement|string) and false will always evaluate to false.~',
            'path' => '../../src/Database/SimplePdo.php',
        ],
        [
            'message' => '~Strict comparison using === between array<string, array<int, mixed>\|string\|false> and false will always evaluate to false.~',
            'path' => '../../src/Application/Configurator.php',
        ]
    ]
    : [
        '~expects DateTimeZone(\|null)?, DateTimeZone\|false given~', # ignore DateTime::getTimeZone() returning false everywhere, because in that case, something is very wrong (probably php.ini)
        '~should return DateTimeZone but returns DateTimeZone\|false.~', # -//-
        '~\(DateTimeZone\) does not accept DateTimeZone\|false.~', # -//-
        '~Cannot call method [a-zA-Z]+\(\) on DateTimeZone\|false.~', # -//-
        '~Parameter #1 \$objectOrClass of class ReflectionClass constructor expects class-string<T of object>\|T of object, string given.~', # in MethodTypeParser; temporary
        '~Strict comparison using === between CurlMultiHandle and false will always evaluate to false.~', # in HttpChannelManager; probably a reflection bug
    ];

return ['parameters' => ['ignoreErrors' => $ignore]];
