<?php declare(strict_types = 1);

namespace Dogma\Tests\Io;

use Dogma\Io\BinaryFile;
use Dogma\Io\FileInfo;
use Dogma\Io\FileMode;
use Dogma\Io\FilesystemException;
use Dogma\Io\Io;
use Dogma\Io\IoException;
use Dogma\Io\LockType;
use Dogma\Io\Stream\StreamInfo;
use Dogma\Io\TextFile;
use Dogma\LogicException;
use Dogma\System\Os;
use Dogma\Tester\Assert;
use function basename;
use function file_exists;
use function get_class;

require_once __DIR__ . '/../bootstrap.php';

$testDir = Io::normalizePath(__DIR__ . '/binfile');
$testDirInfo = new FileInfo($testDir);
Io::createDirectory($testDir, Io::IGNORE);
$testFile = $testDir . '/foo.txt';

$cleanup = static function () use ($testDirInfo, $testFile): void {
    Io::cleanDirectory($testDirInfo, Io::RECURSIVE);
    Assert::true($testDirInfo->isEmpty());
};
$cleanup();


crateTemporaryFile:
$file = BinaryFile::createTemporaryFile();
Assert::true($file->isOpen());


createMemoryFile:
$file = BinaryFile::createMemoryFile();
Assert::true($file->isOpen());

$file = BinaryFile::createMemoryFile(1024);
Assert::true($file->isOpen());


__construct:
// does not exist
Assert::exception(static function () use ($testFile): void {
    new BinaryFile($testFile, FileMode::OPEN_READ);
}, FilesystemException::class);

// does not exist
Assert::exception(static function () use ($testFile): void {
    new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
}, FilesystemException::class);

// create
$file = new BinaryFile($testFile, FileMode::CREATE_WRITE);
Assert::true($file->isOpen());
$file->write('foo');

// cannot read in write only mode
Assert::exception(static function () use ($file): void {
    $file->read();
}, LogicException::class);
$file->close();

// cannot create when already created
Assert::exception(static function () use ($testFile): void {
    $file = new BinaryFile(FileMode::CREATE_READ_WRITE);
    try {
        new BinaryFile($testFile, FileMode::CREATE_READ_WRITE);
    } finally {
        $file->close();
    }
}, FilesystemException::class);

// read
$file = new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
Assert::same($file->read(), 'foo');
$file->close();


toTextFile:
$file = new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
$file = $file->toTextFile();
Assert::type($file, TextFile::class);
$file->close();


getFileInfo:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$info = $file->getFileInfo();
Assert::same(get_class($info), FileInfo::class);


getStreamMetaData:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$metaData = $file->getStreamInfo();
Assert::same(get_class($metaData), StreamInfo::class);


getMode:
$file = new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
Assert::same($file->getMode(), FileMode::OPEN_READ_WRITE);
$file->close();


getPath:
$file = new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
Assert::same($file->getPath(), $testFile);
$file->close();


getName:
$file = new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
Assert::same($file->getName(), basename($testFile));
$file->close();


rename:
$file = new BinaryFile($testFile, FileMode::OPEN_READ_WRITE);
$file->rename($testDir . '/rename1.txt');
$file->close();
Assert::false(file_exists($testFile));
Assert::true(file_exists($testDir . '/rename1.txt'));


close:
isOpen:
reopen:
$file = new BinaryFile($testFile, FileMode::CREATE_WRITE);
Assert::true($file->isOpen());
$file->close();
Assert::false($file->isOpen());
$file->reopen();
Assert::true($file->isOpen());

Assert::exception(static function () use ($file): void {
    $file->reopen();
}, LogicException::class);
$file->close();


read:
write:
endOfFileReached:
$file = new BinaryFile($testFile, FileMode::CREATE_READ_WRITE);
$file->write('1234567890');
$file->setPosition(0);
$str = $file->read(5);
Assert::same($str, '12345');
Assert::false($file->endOfFileReached());
$str = $file->read(5);
Assert::same($str, '67890');
Assert::false($file->endOfFileReached());
$str = $file->read(1);
Assert::same($str, null);
Assert::true($file->endOfFileReached());


copyData:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$file->write('0123456789');

$result = $file->copyData(static function (string $data): void {
    Assert::same($data, '234');
}, 2, 3);
Assert::same($result, 3);

$result = $file->copyData(static function (): void {
    Assert::fail('Callback should not have been called.');
}, 20, 3);
Assert::same($result, 0);

$destinationFile = new BinaryFile($testDir . '/dest.txt', FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$result = $file->copyData($destinationFile, 2, 3);
Assert::same($result, 3);
Assert::same(Io::read($destinationFile), '234');

$destinationFile->truncate();
$result = $file->copyData($destinationFile, 2, 6, 3);
Assert::same($result, 6);
Assert::same(Io::read($destinationFile), '234567');

$destinationFile->truncate();
$result = $file->copyData($destinationFile, 2);
Assert::same($result, 8);
Assert::same(Io::read($destinationFile), '23456789');


getContents:
truncate:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$file->write('0123456789');
Assert::same($file->getContents(), '0123456789');

$file->truncate();
Assert::same($file->getContents(), '');


setPosition:
getPosition:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$file->write('0123456789');
$file->setPosition(5);
Assert::same($file->getPosition(), 5);
Assert::same($file->read(), '56789');
Assert::same($file->getPosition(), 10);
$file->setPosition(0);
Assert::same($file->getPosition(), 0);
Assert::same($file->read(5), '01234');
Assert::same($file->getPosition(), 5);


flush:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$file->flush();


lock:
unlock:
$file = new BinaryFile($testFile, FileMode::CREATE_OR_TRUNCATE_READ_WRITE);
$file->lock(); // shared

$file->unlock();
$file->lock(LockType::EXCLUSIVE);
$file->lock(LockType::SHARED);
$file->unlock();

$file->lock(LockType::SHARED);
Assert::exception(static function () use ($file): void {
    $file->lock(LockType::NON_BLOCKING);
}, IoException::class);

$file->lock(LockType::EXCLUSIVE);
Assert::exception(static function () use ($file): void {
    $file->lock(LockType::NON_BLOCKING);
}, IoException::class);

$file->unlock();
if (!Os::isWindows()) {
    // Windows does not support non-blocking locks
    $file->lock(LockType::NON_BLOCKING);
}


$cleanup();
