<?php declare(strict_types = 1);

namespace Dogma\Tests\Io;

use Dogma\Io\File;
use Dogma\Io\FileInfo;
use Dogma\Io\FilesystemException;
use Dogma\Io\Io;
use Dogma\Io\IoException;
use Dogma\Str;
use Dogma\System\Os;
use Dogma\Tester\Assert;
use Dogma\Time\DateTime;
use Tester\Environment;
use function rd;
use const FILE_APPEND;
use const FILE_SKIP_EMPTY_LINES;
use function file_exists;
use function strlen;

require_once __DIR__ . '/../bootstrap.php';

$testDir = __DIR__ . '/files';
$testDirInfo = new FileInfo($testDir);
Io::createDirectory($testDir, Io::IGNORE);

$cleanup = static function () use ($testDirInfo): void {
    Io::cleanDirectory($testDirInfo, Io::RECURSIVE);
    Assert::true($testDirInfo->isEmpty());
};
$cleanup();


$firstLine = '<?php declare(strict_types = 1);';
$thirdLine = 'namespace Dogma\Tests\Io;';
$fifthLine = 'use Dogma\Io\File;';


getInfo:
Assert::type(Io::getInfo(__FILE__), FileInfo::class);


open:
Assert::type(Io::open(__FILE__), File::class);


read:
$contents = Io::read(__FILE__);
Assert::true(Str::startsWith($contents, $firstLine));

// offset
$contents = Io::read(__FILE__, strlen($firstLine) + 2);
Assert::true(Str::startsWith($contents, $thirdLine));

// offset + length
$contents = Io::read(__FILE__, strlen($firstLine) + 2, strlen($thirdLine));
Assert::same($contents, $thirdLine);

Assert::exception(static function (): void {
    Io::read(__DIR__ . '/non-existing');
}, FilesystemException::class);


readLines:
$lines = Io::readLines(__FILE__);
Assert::same($lines[0], $firstLine);
Assert::same($lines[1], '');
Assert::same($lines[2], $thirdLine);

// offset + length
$lines = Io::readLines(__FILE__, null, 2, 3);
Assert::count($lines, 3);
Assert::same($lines[0], $thirdLine);
Assert::same($lines[1], '');
Assert::same($lines[2], $fifthLine);

// skip empty
$lines = Io::readLines(__FILE__, FILE_SKIP_EMPTY_LINES, 2, 3);
Assert::count($lines, 2);
Assert::same($lines[0], $thirdLine);
Assert::same($lines[1], $fifthLine);

// custom filter (skip empty)
$lines = Io::readLines(__FILE__, function (string $line) { return (bool) $line; }, 2, 3);
Assert::count($lines, 2);
Assert::same($lines[0], $thirdLine);
Assert::same($lines[1], $fifthLine);

Assert::exception(static function (): void {
    Io::readLines(__DIR__ . '/non-existing');
}, FilesystemException::class);


write:
$targetFile = $testDir . '/write1.php';
$result = Io::write($targetFile, $firstLine . "\n\n" . $thirdLine);
Assert::same($result, strlen($firstLine) + strlen($thirdLine) + 2);
Assert::true(file_exists($targetFile));
$contents = Io::read($targetFile);
Assert::same($contents, $firstLine . "\n\n" . $thirdLine);

// append
$result = Io::write($targetFile, "\n\n" . $fifthLine, FILE_APPEND);
Assert::same($result, strlen($fifthLine) + 2);
$contents = Io::read($targetFile);
Assert::same($contents, $firstLine . "\n\n" . $thirdLine . "\n\n" . $fifthLine);

// overwrite
$result = Io::write($targetFile, $firstLine);
Assert::same($result, strlen($firstLine));

// fail without path
Assert::exception(static function () use ($testDir): void {
    Io::write($testDir . '/foo/bar/write2.php', 'foo');
}, FilesystemException::class);

// create path
Io::write($testDir . '/foo/bar/write2.php', 'foo', Io::RECURSIVE);

$cleanup();


touch:
$targetFile = $testDir . '/touch1.php';
$info = new FileInfo($targetFile);
Io::touch($targetFile);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), '');
$info->clearCache();
Assert::true($info->getModifiedTime()->equals($info->getAccessedTime()));

// times
Io::touch($targetFile, new DateTime('-10 seconds'), new DateTime('-20 seconds'));
$info->clearCache();
Assert::false($info->getModifiedTime()->equals($info->getAccessedTime()));

// fail without path
Assert::exception(static function () use ($testDir): void {
    Io::touch($testDir . '/foo/bar/touch2.php');
}, FilesystemException::class);

// create path
$targetFile = $testDir . '/foo/bar/touch2.php';
Io::touch($targetFile, null, null, Io::RECURSIVE);
Assert::true(file_exists($targetFile));

$cleanup();


copy:
$targetFile = $testDir . '/copy1.php';
Io::copy(__FILE__, $targetFile);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

// fail without path
Assert::exception(static function () use ($testDir): void {
    Io::copy(__FILE__, $testDir . '/foo/bar/copy2.php');
}, FilesystemException::class);

// create path
$targetFile = $testDir . '/foo/bar/copy2.php';
Io::copy(__FILE__, $targetFile, Io::RECURSIVE);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

$cleanup();


rename:
$originalFile = $testDir . '/renameOrig.php';
$targetFile = $testDir . '/rename1.php';
Io::copy(__FILE__, $originalFile);
Assert::true(file_exists($originalFile));

Io::rename($originalFile, $targetFile);
Assert::false(file_exists($originalFile));
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

Io::copy(__FILE__, $originalFile);
Assert::true(file_exists($originalFile));

// fail without path
Assert::exception(static function () use ($originalFile, $testDir): void {
    Io::rename($originalFile, $testDir . '/foo/bar/rename2.php');
}, FilesystemException::class);

// create path
Io::rename($originalFile, $testDir . '/foo/bar/rename2.php', Io::RECURSIVE);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

$cleanup();


link:
$originalFile = $testDir . '/linkOrig.php';
$targetFile = $testDir . '/link1.php';
Io::copy(__FILE__, $originalFile);
Assert::true(file_exists($originalFile));

Io::link($originalFile, $targetFile);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

// fail without path
Assert::exception(static function () use ($originalFile, $testDir): void {
    Io::link($originalFile, $testDir . '/foo/bar/link2.php');
}, FilesystemException::class);

// create path
Io::link($originalFile, $testDir . '/foo/bar/link2.php', Io::RECURSIVE);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

// does not rewrite destination
Io::write($testDir . '/link3.php', 'foobar');
Assert::exception(static function () use ($originalFile, $testDir): void {
    Io::link($originalFile, $testDir . '/link3.php');
}, IoException::class);

$cleanup();


unlink:
$originalFile = $testDir . '/unlink.php';
Io::copy(__FILE__, $originalFile);
Assert::true(file_exists($originalFile));

Io::unlink($originalFile);
Assert::false(file_exists($targetFile));

// fail without ignore
Assert::exception(static function () use ($originalFile): void {
    Io::unlink($originalFile);
}, FilesystemException::class);

// pass with ignore
Io::unlink($originalFile, Io::IGNORE);

$cleanup();


// why the fuck?
if (Os::isWindows()) {
    Environment::skip('Io::symlink() needs admin access on Windows.');
}

symlink:
$originalFile = $testDir . '/symlinkOrig.php';
$targetFile = $testDir . '/symlink1.php';
Io::copy(__FILE__, $originalFile);
Assert::true(file_exists($originalFile));

Io::symlink($originalFile, $targetFile);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

// fail without path
Assert::exception(static function () use ($originalFile, $testDir): void {
    Io::symlink($originalFile, $testDir . '/foo/bar/symlink2.php');
}, FilesystemException::class);

// create path
Io::symlink($originalFile, $testDir . '/foo/bar/symlink2.php', Io::RECURSIVE);
Assert::true(file_exists($targetFile));
Assert::same(Io::read($targetFile), Io::read(__FILE__));

$cleanup();
