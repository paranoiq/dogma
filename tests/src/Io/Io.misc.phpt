<?php declare(strict_types = 1);

namespace Dogma\Tests\Io;

use Dogma\InvalidValueException;
use Dogma\Io\FilePermissions;
use Dogma\Io\FilesystemException;
use Dogma\Io\Io;
use Dogma\System\Os;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


$testDir = __DIR__ . '/misc';
Io::createDirectory($testDir, Io::IGNORE);


normalizePath:
Assert::same(Io::normalizePath('one\\two\\three'), 'one/two/three');

Assert::same(Io::normalizePath('one/two/..'), 'one');
Assert::same(Io::normalizePath('one/two/../'), 'one');
Assert::same(Io::normalizePath('one/two/../three'), 'one/three');
Assert::same(Io::normalizePath('one/two/../three/'), 'one/three');
Assert::same(Io::normalizePath('one/two/three/../..'), 'one');
Assert::same(Io::normalizePath('one/two/three/../../'), 'one');
Assert::same(Io::normalizePath('one/two/three/../../four'), 'one/four');
Assert::same(Io::normalizePath('one/two/three/../../four/'), 'one/four');

Assert::same(Io::normalizePath('./'), '');
Assert::same(Io::normalizePath('/.'), '');
Assert::same(Io::normalizePath('/./'), '');

Assert::same(Io::normalizePath('/../'), '..');
Assert::same(Io::normalizePath('/..'), '..');
Assert::same(Io::normalizePath('../'), '..');

Assert::same(Io::normalizePath('/var/.////./user/./././..//.//../////../././.././test/////'), '../../test');

// urls
Assert::same(Io::normalizePath('http://one/two/three'), 'http://one/two/three');

// file uri
Assert::same(Io::normalizePath('file:/one/two/three'), 'file:/one/two/three');
Assert::same(Io::normalizePath('file:///one/two/three'), 'file:///one/two/three');
Assert::same(Io::normalizePath('file://host/one/two/three'), 'file://host/one/two/three');

// mind the dot
Assert::same(Io::normalizePath('nette.safe://file.txt'), 'nette.safe://file.txt');


canonicalizePath:
Assert::same(Io::canonicalizePath('foo'), null);
Assert::same(Io::canonicalizePath(__FILE__), __FILE__);


translatePath:
Assert::same(Io::translatePath('/one/two/three/four', '/one/two', '/five/six'), '/five/six/three/four');
Assert::same(Io::translatePath('/one/two/three/four', '/two/three', '/five/six'), '/one/five/six/four');


getPermissionMask:
setPermissionMask:
$old = Io::getPermissionMask();
$next = Io::setPermissionMask(FilePermissions::OWNER_WRITE);
Assert::same($old, $next);
Assert::same(Io::getPermissionMask(), FilePermissions::OWNER_WRITE);
Io::setPermissionMask($old);
Assert::same(Io::getPermissionMask(), $old);

Assert::exception(static function (): void {
    Io::setPermissionMask(123456789);
}, InvalidValueException::class);


getWorkingDirectory:
setWorkingDirectory:
$old = Io::getWorkingDirectory();
$new = Io::normalizePath($testDir);
Io::setWorkingDirectory($new);
Assert::same(Io::getWorkingDirectory(), $new);
Io::setWorkingDirectory($old);
Assert::same(Io::getWorkingDirectory(), $old);

Assert::exception(static function (): void {
    Io::setWorkingDirectory(__DIR__ . '/non-existing');
}, FilesystemException::class);


getStorageSize:
Assert::true(Io::getStorageSize(__DIR__) > 0);
Assert::exception(static function (): void {
    if (Os::isWindows()) {
        Io::getStorageSize('z:/foo/bar');
    } else {
        Io::getStorageSize('/foo/bar');
    }
}, FilesystemException::class);


getFreeSpace:
Assert::true(Io::getFreeSpace(__DIR__) > 0);
Assert::exception(static function (): void {
    if (Os::isWindows()) {
        Io::getFreeSpace('z:/foo/bar');
    } else {
        Io::getFreeSpace('/foo/bar');
    }
}, FilesystemException::class);
