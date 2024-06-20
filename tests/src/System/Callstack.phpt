<?php declare(strict_types = 1);

namespace Dogma\Tests\System;

use Dogma\Io\Io;
use Dogma\LogicException;
use Dogma\System\Callstack;
use Dogma\System\CallstackFrame;
use Dogma\Tester\Assert;
use Generator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;

require_once __DIR__ . '/../bootstrap.php';

class Test
{

    public static function staticLast(): CallstackFrame
    {
        return Callstack::last();
    }

    public function last(): CallstackFrame
    {
        return Callstack::last();
    }

}

function get(): Callstack
{
    return Callstack::get();
}

function last(): CallstackFrame
{
    return Callstack::last();
}

$last = static function (): CallstackFrame {
    return Callstack::last();
};

function generator(): Generator
{
    foreach ([1] as $n) {
        yield Callstack::last();
    }
}

$class = new class {

    public function last(): CallstackFrame
    {
        return Callstack::last();
    }

};


// Callstack::get()
$callstack = get();
Assert::count($callstack->frames, 1);
$frame = $callstack->frames[0];
Assert::same($frame->getLineCode(), '$callstack = get();');


// CallstackFrame::getFile()
Assert::same($frame->getFile()->getPath(), Io::normalizePath(__FILE__));


// CallstackFrame::getFileInfo()
Assert::same($frame->getFileInfo()->getPath(), Io::normalizePath(__FILE__));


// CallstackFrame::getFileCode()
Assert::same($frame->getFileCode(), Io::read(__FILE__));


// in function
$frame = last();
Assert::same($frame->getFullName(), __NAMESPACE__ . '\\last');
Assert::true($frame->isFunction());
Assert::false($frame->isMethod());
Assert::false($frame->isClosure());
Assert::false($frame->isStatic());
Assert::false($frame->isAnonymous());

Assert::type($frame->getFunctionReflection(), ReflectionFunction::class);
Assert::exception(static function () use ($frame): void {
    $frame->getMethodReflection();
}, LogicException::class);
Assert::exception(static function () use ($frame): void {
    $frame->getClassReflection();
}, LogicException::class);
Assert::exception(static function () use ($frame): void {
    $frame->getObjectReflection();
}, LogicException::class);

Assert::same($frame->getLineCode(), '$frame = last();');
Assert::same($frame->getCode(), 'function last(): CallstackFrame
{
    return Callstack::last();
}');
Assert::exception(static function () use ($frame): void {
    $frame->getClassCode();
}, LogicException::class);


// in closure
$frame = $last();
Assert::same($frame->getFullName(), __NAMESPACE__ . '\\{closure}');
Assert::false($frame->isFunction());
Assert::false($frame->isMethod());
Assert::true($frame->isClosure());
Assert::false($frame->isStatic());
Assert::false($frame->isAnonymous());

Assert::exception(static function () use ($frame): void {
    $frame->getFunctionReflection();
}, LogicException::class);
Assert::exception(static function () use ($frame): void {
    $frame->getMethodReflection();
}, LogicException::class);
Assert::exception(static function () use ($frame): void {
    $frame->getClassReflection();
}, LogicException::class);
Assert::exception(static function () use ($frame): void {
    $frame->getObjectReflection();
}, LogicException::class);

Assert::same($frame->getLineCode(), '$frame = $last();');
Assert::exception(static function () use ($frame): void {
    $frame->getCode();
}, LogicException::class);
Assert::exception(static function () use ($frame): void {
    $frame->getClassCode();
}, LogicException::class);


// in static method
$frame = Test::staticLast();
Assert::same($frame->getFullName(), __NAMESPACE__ . '\\Test::staticLast');
Assert::false($frame->isFunction());
Assert::true($frame->isMethod());
Assert::false($frame->isClosure());
Assert::true($frame->isStatic());
Assert::false($frame->isAnonymous());

Assert::exception(static function () use ($frame): void {
    $frame->getFunctionReflection();
}, LogicException::class);
Assert::type($frame->getMethodReflection(), ReflectionMethod::class);
Assert::type($frame->getClassReflection(), ReflectionClass::class);
Assert::exception(static function () use ($frame): void {
    $frame->getObjectReflection();
}, LogicException::class);

Assert::same($frame->getLineCode(), '$frame = Test::staticLast();');
Assert::same($frame->getCode(), '    public static function staticLast(): CallstackFrame
    {
        return Callstack::last();
    }');
Assert::same($frame->getClassCode(), 'class Test
{

    public static function staticLast(): CallstackFrame
    {
        return Callstack::last();
    }

    public function last(): CallstackFrame
    {
        return Callstack::last();
    }

}');


// in object method
$object = new Test();
$frame = $object->last();
Assert::same($frame->getFullName(), __NAMESPACE__ . '\\Test->last');
Assert::false($frame->isFunction());
Assert::true($frame->isMethod());
Assert::false($frame->isClosure());
Assert::false($frame->isStatic());
Assert::false($frame->isAnonymous());

Assert::exception(static function () use ($frame): void {
    $frame->getFunctionReflection();
}, LogicException::class);
Assert::type($frame->getMethodReflection(), ReflectionMethod::class);
Assert::type($frame->getClassReflection(), ReflectionClass::class);
Assert::type($frame->getObjectReflection(), ReflectionObject::class);

Assert::same($frame->getLineCode(), '$frame = $object->last();');
Assert::same($frame->getCode(), '    public function last(): CallstackFrame
    {
        return Callstack::last();
    }');
Assert::same($frame->getClassCode(), 'class Test
{

    public static function staticLast(): CallstackFrame
    {
        return Callstack::last();
    }

    public function last(): CallstackFrame
    {
        return Callstack::last();
    }

}');


// in anonymous class method
$frame = $class->last();
Assert::false($frame->isFunction());
Assert::true($frame->isMethod());
Assert::false($frame->isClosure());
Assert::false($frame->isStatic());
Assert::true($frame->isAnonymous());

Assert::exception(static function () use ($frame): void {
    $frame->getFunctionReflection();
}, LogicException::class);
Assert::type($frame->getMethodReflection(), ReflectionMethod::class);
Assert::type($frame->getClassReflection(), ReflectionClass::class);
Assert::type($frame->getObjectReflection(), ReflectionObject::class);

Assert::same($frame->getLineCode(), '$frame = $class->last();');
Assert::same($frame->getCode(), '    public function last(): CallstackFrame
    {
        return Callstack::last();
    }');
Assert::same($frame->getClassCode(), '$class = new class {

    public function last(): CallstackFrame
    {
        return Callstack::last();
    }

};');
