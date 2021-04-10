<?php declare(strict_types = 1);

namespace Dogma\Tests\DogmaLoader;

use Dogma\Callback;
use Dogma\Tester\Assert;
use function rand;

require_once __DIR__ . '/../bootstrap.php';


$a = function ()
{
    return rand(0, 10);
};

$b = function () {
    return rand(0, 20);
};

$c = function () { return rand(0, 30); };

class Foo
{

    static function d()
    {
        return rand(0, 40);
    }

    static function e() {
        return rand(0, 50);
    }

    static function f() { return rand(0, 60); }

}

Assert::same(Callback::getBody($a), 'function ()
{
    return rand(0, 10);
}');

Assert::same(Callback::getBody($b), 'function () {
    return rand(0, 20);
}');

Assert::same(Callback::getBody($c), 'function () { return rand(0, 30); }');

Assert::same(Callback::getBody([Foo::class, 'd']), 'function d()
    {
        return rand(0, 40);
    }');

Assert::same(Callback::getBody([Foo::class, 'e']), 'function e() {
        return rand(0, 50);
    }');

Assert::same(Callback::getBody([Foo::class, 'f']), 'function f() { return rand(0, 60); }');
