<?php declare(strict_types = 1);

namespace Dogma\Tests\Io;

use Dogma\Io\File;
use Dogma\Io\FileInfo;
use Dogma\Io\FileMode;
use Dogma\Io\Io;
use Dogma\Io\TextFile;
use Dogma\Language\Encoding;
use Dogma\Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

$testDir = Io::normalizePath(__DIR__ . '/textfile');
$testDirInfo = new FileInfo($testDir);
Io::createDirectory($testDir, Io::IGNORE);

$cleanup = static function () use ($testDirInfo): void {
    Io::cleanDirectory($testDirInfo, Io::RECURSIVE);
    Assert::true($testDirInfo->isEmpty());
};
$cleanup();


$testFile = $testDir . '/foo.txt';

toBinaryFile:
$file = new TextFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$file = $file->toBinaryFile();
Assert::type($file, File::class);
$file->write("\x00f\x00o\x00o");
$file->setPosition(0);
$file = $file->toTextFile();
Assert::type($file, TextFile::class);


setEncoding:
getEncoding:
$file->setEncoding(Encoding::UTF_8);
Assert::same($file->getEncoding(), Encoding::UTF_8);
Assert::same($file->readLine(), "\x00f\x00o\x00o");
$file->setPosition(0);
$file->setEncoding(Encoding::UTF_16);
Assert::same($file->getEncoding(), Encoding::UTF_16);
Assert::same($file->readLine(), 'foo');


convertEncoding:


setLineEndings:
getLineEndings:


convertLineEndings:


getContents:


read:


write:


readLine:


writeLine:


readLines:


writeLines:


truncate:


truncateLines:


readCsvRow:
writeCsvRow:
