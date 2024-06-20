<?php declare(strict_types = 1);

namespace Dogma\Tests\Io;

use Dogma\Io\FileInfo;
use Dogma\Io\FilePermissions;
use Dogma\Io\FilesystemException;
use Dogma\Io\Io;
use Dogma\Str;
use Dogma\System\Os;
use Dogma\Tester\Assert;
use Tester\Environment;
use function array_keys;
use function file_exists;
use function is_dir;
use function iterator_to_array;

require_once __DIR__ . '/../bootstrap.php';

$testDir = Io::normalizePath(__DIR__) . '/dirs';
$testDirInfo = new FileInfo($testDir);
Io::createDirectory($testDir, Io::IGNORE);

$cleanup = static function () use ($testDirInfo): void {
    Io::cleanDirectory($testDirInfo, Io::RECURSIVE);
    Assert::true($testDirInfo->isEmpty());
};
$cleanup();


createDirectory:
$targetDir = $testDir . '/foo';
Io::createDirectory($targetDir);
Assert::true(is_dir($targetDir));

// fail without ignore
Assert::exception(static function () use ($targetDir): void {
    Io::createDirectory($targetDir);
}, FilesystemException::class);

// pass with ignore
Io::createDirectory($targetDir, Io::IGNORE);

// fail without recursive
$targetDir = $testDir . '/bar/baz';
Assert::exception(static function () use ($targetDir): void {
    Io::createDirectory($targetDir);
}, FilesystemException::class);

// pass with recursive
Io::createDirectory($targetDir, Io::RECURSIVE);
Assert::true(is_dir($targetDir));

$cleanup();


deleteDirectory:
$targetDir = $testDir . '/foo';
Io::createDirectory($targetDir);
Assert::true(is_dir($targetDir));
Io::deleteDirectory($targetDir);
Assert::false(file_exists($targetDir));

// fail without ignore
Assert::exception(static function () use ($targetDir): void {
    Io::deleteDirectory($targetDir);
}, FilesystemException::class);

// pass with ignore
Io::deleteDirectory($targetDir, Io::IGNORE);

// fail without recursive
$innerDir = $testDir . '/bar/baz';
$targetDir = $testDir . '/bar';
Io::createDirectory($innerDir, Io::RECURSIVE);
Assert::true(is_dir($innerDir));
Assert::exception(static function () use ($targetDir): void {
    Io::deleteDirectory($targetDir);
}, FilesystemException::class);

// pass with recursive
Io::deleteDirectory($targetDir, Io::RECURSIVE);
Assert::false(file_exists($targetDir));

$cleanup();


cleanDirectory:
Io::createDirectory($testDir . '/foo');
Io::touch($testDir . '/foo.txt');
Io::cleanDirectory($testDir);
Assert::true($testDirInfo->isEmpty());

// fail without recursive
Io::createDirectory($testDir . '/foo/bar', Io::RECURSIVE);
Io::touch($testDir . '/foo/bar.txt');
Assert::exception(static function () use ($testDir): void {
    Io::cleanDirectory($testDir);
}, FilesystemException::class);

// pass with recursive
Io::cleanDirectory($testDir, Io::RECURSIVE);
Assert::true($testDirInfo->isEmpty());

// filters - files only
Io::createDirectory($testDir . '/foo/bar', Io::RECURSIVE);
Io::touch($testDir . '/foo.txt');
Io::touch($testDir . '/foo/foo.txt');
Io::touch($testDir . '/bar.txt');
Io::touch($testDir . '/foo/bar.txt');
Io::cleanDirectory($testDir, Io::RECURSIVE, Io::FILES_ONLY);
Assert::false(file_exists($testDir . '/bar.txt'));
Assert::false(file_exists($testDir . '/foo/bar.txt'));
Assert::false(file_exists($testDir . '/foo.txt'));
Assert::false(file_exists($testDir . '/foo/foo.txt'));
Assert::true(is_dir($testDir . '/foo/bar'));

// filters - specific files
Io::touch($testDir . '/foo.txt');
Io::touch($testDir . '/foo/foo.txt');
Io::touch($testDir . '/bar.txt');
Io::touch($testDir . '/foo/bar.txt');
Io::cleanDirectory($testDir, Io::RECURSIVE, static function (FileInfo $item): bool {
    return $item->isFile() && Str::startsWith($item->getName(), 'foo');
});
Assert::true(file_exists($testDir . '/bar.txt'));
Assert::true(file_exists($testDir . '/foo/bar.txt'));
Assert::false(file_exists($testDir . '/foo.txt'));
Assert::false(file_exists($testDir . '/foo/foo.txt'));
Assert::true(is_dir($testDir . '/foo/bar'));

// filters - non-empty dirs are kept
Io::touch($testDir . '/foo.txt');
Io::touch($testDir . '/foo/foo.txt');
Io::touch($testDir . '/bar.txt');
Io::touch($testDir . '/foo/bar.txt');
Io::cleanDirectory($testDir, Io::RECURSIVE, static function (FileInfo $item): bool {
    return Str::startsWith($item->getName(), 'foo');
});
Assert::true(file_exists($testDir . '/bar.txt'));
Assert::true(file_exists($testDir . '/foo/bar.txt'));
Assert::false(file_exists($testDir . '/foo.txt'));
Assert::false(file_exists($testDir . '/foo/foo.txt'));
Assert::true(is_dir($testDir . '/foo/bar'));

$cleanup();


copyDirectory:
$sourceDir = $testDir . '/src';
$destDir = $testDir . '/dest';
Io::createDirectory($sourceDir);
Io::touch($sourceDir . '/foo.txt');
Io::copyDirectory($sourceDir, $destDir);
Assert::true(file_exists($destDir . '/foo.txt'));

// fail so rewrite without ignore
Assert::exception(static function () use ($sourceDir, $destDir): void {
    Io::copyDirectory($sourceDir, $destDir);
}, FilesystemException::class);

// pass with ignore
Io::copyDirectory($sourceDir, $destDir, Io::IGNORE);

// copy only direct descendants without recursive
$cleanup();
Io::createDirectory($sourceDir . '/foo/bar', Io::RECURSIVE);
Io::touch($sourceDir . '/foo.txt');
Io::touch($sourceDir . '/foo/bar.txt');
Io::copyDirectory($sourceDir, $destDir);
Assert::true(file_exists($destDir . '/foo.txt'));
Assert::false(file_exists($destDir . '/foo/bar.txt'));
Assert::false(is_dir($destDir . '/foo/bar'));

// copy everything with recursive
$cleanup();
Io::createDirectory($sourceDir . '/foo/bar', Io::RECURSIVE);
Io::touch($sourceDir . '/foo.txt');
Io::touch($sourceDir . '/foo/bar.txt');
Io::copyDirectory($sourceDir, $destDir, Io::RECURSIVE);
Assert::true(file_exists($destDir . '/foo.txt'));
Assert::true(file_exists($destDir . '/foo/bar.txt'));
Assert::true(is_dir($destDir . '/foo/bar'));

// filters - directories only
$cleanup();
Io::createDirectory($sourceDir . '/foo/bar', Io::RECURSIVE);
Io::touch($sourceDir . '/foo.txt');
Io::touch($sourceDir . '/foo/foo.txt');
Io::touch($sourceDir . '/bar.txt');
Io::touch($sourceDir . '/foo/bar.txt');
Io::copyDirectory($sourceDir, $destDir, Io::RECURSIVE, Io::DIRECTORIES_ONLY);
Assert::false(file_exists($destDir . '/bar.txt'));
Assert::false(file_exists($destDir . '/foo/bar.txt'));
Assert::false(file_exists($destDir . '/foo.txt'));
Assert::false(file_exists($destDir . '/foo/foo.txt'));
Assert::true(is_dir($destDir . '/foo/bar'));

// filters - specific files, create paths
$cleanup();
Io::createDirectory($sourceDir . '/foo/bar', Io::RECURSIVE);
Io::touch($sourceDir . '/foo.txt');
Io::touch($sourceDir . '/foo/foo.txt');
Io::touch($sourceDir . '/bar.txt');
Io::touch($sourceDir . '/foo/bar.txt');
Io::copyDirectory($sourceDir, $destDir, Io::RECURSIVE, static function (FileInfo $item) {
    return $item->isFile() && Str::startsWith($item->getName(), 'foo');
});
Assert::false(file_exists($destDir . '/bar.txt'));
Assert::false(file_exists($destDir . '/foo/bar.txt'));
Assert::true(file_exists($destDir . '/foo.txt'));
Assert::true(file_exists($destDir . '/foo/foo.txt'));
Assert::false(is_dir($destDir . '/foo/bar'));

$cleanup();


scanDirectory:
Io::touch($testDir . '/foo/bar/foo.txt', null, null, Io::RECURSIVE);
Io::touch($testDir . '/foo/bar.txt');
Io::touch($testDir . '/foo.txt');
$results = Io::scanDirectory($testDir);
Assert::same(array_keys(iterator_to_array($results)), [
    $testDir . '/foo',
    $testDir . '/foo.txt',
]);

$results = Io::scanDirectory($testDir, Io::RECURSIVE);
Assert::same(array_keys(iterator_to_array($results)), [
    $testDir . '/foo',
    $testDir . '/foo/bar',
    $testDir . '/foo/bar/foo.txt',
    $testDir . '/foo/bar.txt',
    $testDir . '/foo.txt',
]);

$results = Io::scanDirectory($testDir, Io::RECURSIVE | Io::CHILDREN_FIRST);
Assert::same(array_keys(iterator_to_array($results)), [
    $testDir . '/foo/bar/foo.txt',
    $testDir . '/foo/bar',
    $testDir . '/foo/bar.txt',
    $testDir . '/foo',
    $testDir . '/foo.txt',
]);

$cleanup();


if (Os::isWindows()) {
    Environment::skip('stat() always returns mode 777/666 on Windows.');
}

updatePermissions:
Io::setPermissionMask(0);
Io::createDirectory($testDir . '/foo');
Io::touch($testDir . '/foo/bar.txt');
Io::touch($testDir . '/foo.txt');
$fooDir = new FileInfo($testDir . '/foo');
$fooBar = new FileInfo($testDir . '/foo/bar.txt');
$foo = new FileInfo($testDir . '/foo.txt');
Assert::same($fooDir->getPermissionsOct(), '777');
Assert::same($fooBar->getPermissionsOct(), '666');
Assert::same($foo->getPermissionsOct(), '666');

// remove
Io::updatePermissions($foo, 0, FilePermissions::OTHER_ALL | FilePermissions::GROUP_WRITE);
Assert::same($fooDir->getPermissionsOct(), '777');
Assert::same($fooBar->getPermissionsOct(), '666');
Assert::same($foo->getPermissionsOct(), '640');

// add
Io::updatePermissions($foo, FilePermissions::OTHER_ALL | FilePermissions::GROUP_WRITE, 0);
Assert::same($fooDir->getPermissionsOct(), '777');
Assert::same($fooBar->getPermissionsOct(), '666');
Assert::same($foo->getPermissionsOct(), '666');

// recursive remove
Io::updatePermissions($testDir, 0, FilePermissions::OTHER_ALL | FilePermissions::GROUP_WRITE, Io::RECURSIVE);
Assert::same($fooDir->getPermissionsOct(), '750');
Assert::same($fooBar->getPermissionsOct(), '640');
Assert::same($foo->getPermissionsOct(), '640');

// recursive add
Io::updatePermissions($testDir, 0, FilePermissions::OTHER_ALL | FilePermissions::GROUP_WRITE, Io::RECURSIVE);
Assert::same($fooDir->getPermissionsOct(), '777');
Assert::same($fooBar->getPermissionsOct(), '666');
Assert::same($foo->getPermissionsOct(), '666');

$cleanup();
