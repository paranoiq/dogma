<?php declare(strict_types = 1);
/**
 * This file is part of the Dogma library (https://github.com/paranoiq/dogma)
 *
 * Copyright (c) 2012 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

namespace Dogma\Io;

use Dogma\CallbackIterator;
use Dogma\Check;
use Dogma\InvalidArgumentException;
use Dogma\StaticClassMixin;
use Dogma\Str;
use Dogma\Time\DateTime;
use FilesystemIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use StreamContext;
use const FILE_APPEND;
use const FILE_IGNORE_NEW_LINES;
use const FILE_SKIP_EMPTY_LINES;
use const LOCK_EX;
use function array_filter;
use function array_slice;
use function chdir;
use function chgrp;
use function chmod;
use function chown;
use function clearstatcache;
use function copy;
use function dirname;
use function disk_free_space;
use function disk_total_space;
use function error_clear_last;
use function error_get_last;
use function file;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function getcwd;
use function is_callable;
use function is_dir;
use function is_file;
use function is_string;
use function link;
use function mkdir;
use function realpath;
use function rename;
use function rmdir;
use function str_replace;
use function strpos;
use function substr;
use function symlink;
use function touch;
use function umask;
use function unlink;

/**
 * Basic IO operations on files and directories.
 * Wrapper over native filesystem functions providing better error handling and usability.
 *
 * All operations throw FilesystemException on file system related failures.
 * Operations like write(), rename(), link() etc. do overwrite targets if they already exist (default and only PHP behavior).
 *
 * These operations do not try to solve isolation or atomicity more than the underlying PHP functions.
 * For that use eg. Nette safe stream wrapper: https://doc.nette.org/en/3.0/safestream
 */
class Io
{
    use StaticClassMixin;

    public const IGNORE = 1;
    public const RECURSIVE = 2;
    public const FOLLOW_SYMLINKS = 4;
    public const CHILDREN_FIRST = 8;

    public const FILES_ONLY = [self::class, 'filterFiles'];
    public const DIRECTORIES_ONLY = [self::class, 'filterDirectories'];

    /**
     * Normalize file path:
     * - changes \ to /
     * - removes . dirs
     * - resolves .. dirs if possible
     * - removes duplicit /
     * - removes ending /
     * - removes starting / from relative paths
     * - keeps / after uri scheme intact (https://en.wikipedia.org/wiki/File_URI_scheme)
     *
     * @param string $path
     * @param bool $relative
     * @return string
     */
    public static function normalizePath(string $path, bool $relative = false): string
    {
        $path = str_replace('\\', '/', $path);

        $patterns = [
            '~/\./~' => '/', // remove . dirs
            '~(?<!:)(?<!:/)/{2,}~' => '/', // remove duplicit /, but keep up to three / after uri scheme
            '~([^/\.]+/(?R)*\.\./?)~' => '', // resolve .. dirs
        ];
        do {
            $previous = $path;
            $path = Str::replace($path, $patterns);
        } while ($path !== $previous);

        $path = Str::replace($path, [
            '~/+\.?$~' => '', // end /.
            '~^\.$~' => '', // sole .
            '~^/\.~' => '.', // /.. on start
        ]);

        return $relative && $path[0] === '/' ? substr($path, 1) : $path;
    }

    /**
     * Normalizes path and dereferences symbolic links in the path
     * Returns NULL if file/directory does not exist
     *
     * @param string $path
     * @return string|null
     */
    public static function canonicalizePath(string $path): ?string
    {
        return realpath($path) ?: null;
    }

    /**
     * Translate path from source directory to destination directory
     *
     * @param string $path
     * @param string $sourcePrefix
     * @param string $destinationPrefix
     * @return string
     */
    public static function translatePath(string $path, string $sourcePrefix, string $destinationPrefix): string
    {
        if (strpos($path, $sourcePrefix) === false) {
            throw new InvalidArgumentException("Source path prefix not found in translated file path.");
        }

        return str_replace($sourcePrefix, $destinationPrefix, $path);
    }

    protected static function filterFiles(FileInfo $item): bool
    {
        return $item->isFile();
    }

    protected static function filterDirectories(FileInfo $item): bool
    {
        return $item->isDirectory();
    }

    // settings --------------------------------------------------------------------------------------------------------

    public static function clearCache(): void
    {
        clearstatcache(true);
    }

    public static function getPermissionMask(): int
    {
        return umask();
    }

    /**
     * Set permission mask (umask) and return old value
     *
     * @param int $mask
     * @return int
     */
    public static function setPermissionMask(int $mask): int
    {
        Check::flags($mask, FilePermissions::ALL);

        return umask($mask);
    }

    public static function getWorkingDirectory(): string
    {
        $path = getcwd();
        if ($path === false) {
            throw new FilesystemException('Cannot get current working directory.', null);
        }

        return self::normalizePath($path);
    }

    /**
     * @param string|Path $path
     */
    public static function setWorkingDirectory($path): void
    {
        if ($path instanceof Path) {
            $path = $path->getPath();
        } else {
            $path = self::normalizePath($path);
        }

        error_clear_last();
        $result = @chdir($path);

        if ($result === false) {
            throw FilesystemException::create("Cannot set working directory", $path, null, error_get_last());
        }
    }

    // storage ---------------------------------------------------------------------------------------------------------

    /**
     * @param string|Path $path
     * @return int
     */
    public static function getStorageSize($path): int
    {
        if ($path instanceof Path) {
            $path = $path->getPath();
        }

        error_clear_last();
        /** @var int|false $result */
        $result = @disk_total_space($path);

        if ($result === false) {
            throw FilesystemException::create("Cannot get storage size", $path, null, error_get_last());
        }

        return (int) $result;
    }

    /**
     * @param string|Path $path
     * @return int
     */
    public static function getFreeSpace($path): int
    {
        if ($path instanceof Path) {
            $path = $path->getPath();
        }

        error_clear_last();
        /** @var int|false $result */
        $result = @disk_free_space($path);

        if ($result === false) {
            throw FilesystemException::create("Cannot get free space", $path, null, error_get_last());
        }

        return (int) $result;
    }

    // files -----------------------------------------------------------------------------------------------------------

    /**
     * @param string|Path $file
     * @return FileInfo
     */
    public static function getInfo($file): FileInfo
    {
        return new FileInfo($file);
    }

    /**
     * @param string|Path $file
     * @param string $mode
     * @param StreamContext|null $context
     * @return BinaryFile
     */
    public static function open($file, string $mode = FileMode::OPEN_READ, ?StreamContext $context = null): BinaryFile
    {
        return new BinaryFile($file, $mode, $context);
    }

    /**
     * @param string|Path $file
     * @return bool
     */
    public static function exists($file): bool
    {
        if ($file instanceof Path) {
            $file = $file->getPath();
        }

        return file_exists($file);
    }

    /**
     * Get path of first existing file
     *
     * @param string|Path ...$files
     * @return string
     */
    public static function existing(...$files): string
    {
        foreach ($files as $file) {
            if ($file instanceof Path) {
                $file = $file->getPath();
            }

            if (file_exists($file)) {
                return $file;
            }
        }

        throw new IoException('None of given files exist.');
    }

    /**
     * Replace strings in file line per line. Similar to unix utility `sed` in substitution mode
     * Returns count of changed lines
     *
     * @param string|Path $file
     * @param string|string[] $pattern
     * @param string|callable|null $replacement
     * @param int $offset
     * @param int|null $length
     * @param StreamContext|null $context
     * @return int
     */
    public static function edit($file, $pattern, $replacement = null, int $offset = 0, ?int $length = null, ?StreamContext $context = null): int
    {
        $changed = $inserted = $deleted = 0;

        $file = self::open($file, FileMode::OPEN_READ_WRITE, $context)->toTextFile();
        $file->lock(LOCK_EX);

        $lines = $file->readLines();
        $before = [];
        $after = [];
        if ($offset !== 0) {
            $before = array_slice($lines, 0, $offset);
            if ($length !== null) {
                $lines = array_slice($lines, $offset, $length);
                $after = array_slice($lines, $offset + $length);
            } else {
                $lines = array_slice($lines, $offset);
            }
        }

        $edited = [];
        foreach ($lines as $line) {
            $result = Str::replace($line, $pattern, $replacement);
            if ($result !== $line) {
                $changed++;
            }
        }

        $file->truncate();
        $file->writeLines($before);
        $file->writeLines($edited);
        $file->writeLines($after);
        $file->unlock();
        $file->close();

        return $changed;
    }

    /**
     * Read lines of a file; with regexp, callable or FILE_SKIP_EMPTY_LINES as filter
     *
     * @param string|Path $file
     * @param string|callable|int $filter
     * @param int $start
     * @param int|null $count
     * @param StreamContext|null $context
     * @return string[]
     */
    public static function readLines($file, $filter = null, int $start = 0, ?int $count = null, ?StreamContext $context = null): array
    {
        if ($file instanceof Path) {
            $file = $file->getPath();
        }

        error_clear_last();
        $result = $context !== null
            ? @file($file, FILE_IGNORE_NEW_LINES, $context->getResource())
            : @file($file, FILE_IGNORE_NEW_LINES);

        if ($result === false) {
            throw FilesystemException::create("Cannot read file lines", $file, $context, error_get_last());
        }

        if ($start !== 0 || $count !== null) {
            $result = array_slice($result, $start, (int) $count, false);
        }

        if ($filter === FILE_SKIP_EMPTY_LINES) {
            return array_values(array_filter($result, static function (string $line) {
                return $line !== '';
            }));
        } elseif (is_string($filter)) {
            return array_values(array_filter($result, static function (string $line) use ($filter) {
                return Str::match($line, $filter);
            }));
        } elseif (is_callable($filter)) {
            return array_values(array_filter($result, static function (string $line) use ($filter) {
                return $filter($line);
            }));
        } elseif ($filter !== null) {
            throw new InvalidArgumentException('Filter should be regexp, callable or FILE_SKIP_EMPTY_LINES constant.');
        }

        return $result;
    }

    /**
     * Read contents of a file
     *
     * @param string|Path $file
     * @param int $offset
     * @param int|null $length
     * @param StreamContext|null $context
     * @return string
     */
    public static function read($file, int $offset = 0, ?int $length = null, ?StreamContext $context = null): string
    {
        if ($file instanceof Path) {
            $file = $file->getPath();
        }

        error_clear_last();
        $rawContext = $context !== null ? $context->getResource() : null;
        // $length cannot be of null nor negative and 0 means zero length
        if ($length !== null) {
            $result = @file_get_contents($file, false, $rawContext, $offset, $length);
        } else {
            $result = @file_get_contents($file, false, $rawContext, $offset);
        }

        if ($result === false) {
            throw FilesystemException::create("Cannot read file", $file, $context, error_get_last());
        }

        return $result;
    }

    /**
     * Writes contents to a file
     * Appends if FILE_APPEND is set
     * Locks before writing if LOCK_EX is set
     * Creates path if RECURSIVE is set
     *
     * @param string|Path $file
     * @param string $data
     * @param int $flags (FILE_APPEND, LOCK_EX)
     * @param StreamContext|null $context
     * @return int
     */
    public static function write($file, string $data, int $flags = 0, ?StreamContext $context = null): int
    {
        Check::flags($flags, self::RECURSIVE | FILE_APPEND | LOCK_EX);

        if ($file instanceof Path) {
            $file = $file->getPath();
        }

        $dir = dirname($file);
        if (($flags & self::RECURSIVE) && !is_dir($dir)) {
            self::createDirectory($dir, self::RECURSIVE, FilePermissions::ALL, $context);
        }

        error_clear_last();
        $result = $context !== null
            ? @file_put_contents($file, $data, ($flags & ~self::RECURSIVE), $context->getResource())
            : @file_put_contents($file, $data, ($flags & ~self::RECURSIVE));

        if ($result === false) {
            throw FilesystemException::create("Cannot write file", $file, $context, error_get_last());
        }

        return $result;
    }

    /**
     * Touch file (create empty file or modify timestamps of existing)
     * Creates path if RECURSIVE is set
     *
     * @param string|Path $file
     * @param int|DateTime|null $modified
     * @param int|DateTime|null $accessed
     * @param int $flags
     */
    public static function touch($file, $modified = null, $accessed = null, int $flags = 0): void
    {
        Check::flags($flags, self::RECURSIVE);

        if ($file instanceof Path) {
            $file = $file->getPath();
        }
        if ($modified instanceof DateTime) {
            $modified = $modified->getTimestamp();
        }
        if ($accessed instanceof DateTime) {
            $accessed = $accessed->getTimestamp();
        }

        $dir = dirname($file);
        if (($flags & self::RECURSIVE) && !is_dir($dir)) {
            self::createDirectory($dir, self::RECURSIVE, FilePermissions::ALL);
        }

        error_clear_last();
        // time params are not nullable and 0 means 1970-01-01
        if ($modified !== null && $accessed !== null) {
            $result = @touch($file, $modified, $accessed);
        } elseif ($modified !== null) {
            $result = @touch($file, $modified);
        } elseif ($accessed !== null) {
            throw new InvalidArgumentException('Parameter $modified must be not null when $accessed is not null.');
        } else {
            $result = @touch($file);
        }

        if ($result === false) {
            throw FilesystemException::create("Cannot touch file", $file, null, error_get_last());
        }
    }

    /**
     * Copy a file
     * Overwrites destination file if it already exists
     * Creates path if RECURSIVE is set
     *
     * @param string|Path $source
     * @param string|Path $destination
     * @param int $flags
     * @param StreamContext|null $context
     */
    public static function copy($source, $destination, int $flags = 0, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::RECURSIVE);

        if ($source instanceof Path) {
            $source = $source->getPath();
        }
        if ($destination instanceof Path) {
            $destination = $destination->getPath();
        }

        $dir = dirname($destination);
        if (($flags & self::RECURSIVE) && !is_dir($dir)) {
            self::createDirectory($dir, self::RECURSIVE, FilePermissions::ALL, $context);
        }

        error_clear_last();
        $result = $context !== null
            ? @copy($source, $destination, $context->getResource())
            : @copy($source, $destination);

        if ($result === false) {
            throw FilesystemException::create("Cannot copy file to destination", $destination, $context, error_get_last());
        }
    }

    /**
     * Move file to a new location/name
     * Creates path to destination if RECURSIVE is set
     *
     * @param string|Path $source
     * @param string|Path $destination
     * @param int $flags
     * @param StreamContext|null $context
     */
    public static function rename($source, $destination, int $flags = 0, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::RECURSIVE);

        if ($source instanceof Path) {
            $source = $source->getPath();
        }
        if ($destination instanceof Path) {
            $destination = $destination->getPath();
        }

        $dir = dirname($destination);
        if (($flags & self::RECURSIVE) && !is_dir($dir)) {
            self::createDirectory($dir, self::RECURSIVE, FilePermissions::ALL, $context);
        }

        error_clear_last();
        $result = $context !== null
            ? @rename($source, $destination, $context->getResource())
            : @rename($source, $destination);

        if ($result === false) {
            throw FilesystemException::create("Cannot rename file to destination", $destination, $context, error_get_last());
        }
    }

    /**
     * Create a hardlink to file
     * Creates path to destination if RECURSIVE is set
     * Dereferences the path to source when FOLLOW_SYMLINKS is set
     *
     * @param string|Path $source
     * @param string|Path $destination
     * @param int $flags
     */
    public static function link($source, $destination, int $flags = 0): void
    {
        Check::flags($flags, self::RECURSIVE);

        if ($source instanceof Path) {
            $source = $source->getPath();
        }
        if ($destination instanceof Path) {
            $destination = $destination->getPath();
        }

        $dir = dirname($destination);
        if (($flags & self::RECURSIVE) && !is_dir($dir)) {
            self::createDirectory($dir, self::RECURSIVE, FilePermissions::ALL);
        }

        // todo: follow symlinks

        error_clear_last();
        $result = @link($source, $destination);

        if ($result === false) {
            throw FilesystemException::create("Cannot link file", $destination, null, error_get_last());
        }
    }

    /**
     * Create a symbolic link to file (softlink)
     * Creates path to destination if RECURSIVE is set
     * Dereferences the path to source when FOLLOW_SYMLINKS is set
     *
     * @param string|Path $source
     * @param string|Path $destination
     * @param int $flags
     */
    public static function symlink($source, $destination, int $flags = 0): void
    {
        Check::flags($flags, self::RECURSIVE);

        if ($source instanceof Path) {
            $source = $source->getPath();
        }
        if ($destination instanceof Path) {
            $destination = $destination->getPath();
        }

        // todo: follow symlinks

        $dir = dirname($destination);
        if (($flags & self::RECURSIVE) && !is_dir($dir)) {
            self::createDirectory($dir, self::RECURSIVE, FilePermissions::ALL);
        }

        error_clear_last();
        $result = @symlink($source, $destination);

        if ($result === false) {
            throw FilesystemException::create("Cannot symlink file", $destination, null, error_get_last());
        }
    }

    /**
     * Delete a file (remove link to its contents)
     * Ignores error when IGNORE is set and file does not exist.
     *
     * @param string|Path $file
     * @param int $flags
     * @param StreamContext|null $context
     */
    public static function unlink($file, int $flags = 0, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::IGNORE);

        if ($file instanceof Path) {
            $file = $file->getPath();
        }

        error_clear_last();
        $result = $context !== null
            ? @unlink($file, $context->getResource())
            : @unlink($file);

        if ($result === false) {
            if (!($flags & self::IGNORE) || file_exists($file)) {
                throw FilesystemException::create("Cannot delete file", $file, $context, error_get_last());
            }
        }
    }

    /**
     * Delete a file. Alias for unlink()
     *
     * @param string|Path $file
     * @param int $flags
     * @param StreamContext|null $context
     */
    public static function delete($file, int $flags = 0, ?StreamContext $context = null): void
    {
        self::unlink($file, $flags, $context);
    }

    // directory actions -----------------------------------------------------------------------------------------------

    /**
     * Create directory (or path with RECURSIVE)
     * Ignores error when IGNORE is set and directory already exists
     *
     * @param string|Path $path
     * @param int $flags
     * @param int $permissions
     * @param StreamContext|null $context
     */
    public static function createDirectory($path, int $flags = 0, int $permissions = 0777, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::IGNORE | self::RECURSIVE);

        if ($path instanceof Path) {
            $path = $path->getPath();
        }

        error_clear_last();
        $result = $context !== null
            ? @mkdir($path, $permissions, ($flags & self::RECURSIVE) !== 0, $context->getResource())
            : @mkdir($path, $permissions, ($flags & self::RECURSIVE) !== 0);

        if ($result === false && (!($flags & self::IGNORE) || !is_dir($path))) {
            throw FilesystemException::create("Cannot create directory", $path, $context, error_get_last());
        }
    }

    /**
     * Delete directory (including its contents with RECURSIVE)
     * Delete linked content when FOLLOW_SYMLINKS is set
     * Ignores error when IGNORE is set and directory does not exist
     *
     * @param string|Path $path
     * @param int $flags
     * @param StreamContext|null $context
     */
    public static function deleteDirectory($path, int $flags = 0, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::IGNORE | self::RECURSIVE | self::FOLLOW_SYMLINKS);

        if ($path instanceof Path) {
            $path = $path->getPath();
        }

        if (is_file($path)) {
            throw FilesystemException::create("Expected directory path", $path, $context);
        }

        if ($flags & self::RECURSIVE) {
            $items = self::scanDirectory($path, $flags | self::CHILDREN_FIRST);
            foreach ($items as $filePath => $fileInfo) {
                if (is_dir($filePath)) {
                    self::removeDirectory($filePath, self::IGNORE, $context);
                } else {
                    self::unlink($filePath, self::IGNORE, $context);
                }
            }
        }

        self::removeDirectory($path, $flags, $context);
    }

    /**
     * Delete files (and directories with RECURSIVE)
     * Delete linked content when FOLLOW_SYMLINKS is set
     *
     * @param string|Path $path
     * @param int $flags
     * @param callable|null $filter
     * @param StreamContext|null $context
     */
    public static function cleanDirectory($path, int $flags = 0, ?callable $filter = null, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::RECURSIVE | self::FOLLOW_SYMLINKS);

        $items = self::scanDirectory($path, ($flags | self::CHILDREN_FIRST) & ~self::IGNORE);
        foreach ($items as $filePath => $fileInfo) {
            if ($filter !== null && !$filter($fileInfo)) {
                continue;
            }

            if (is_dir($filePath)) {
                if ($filter === null || $fileInfo->isEmpty()) {
                    self::removeDirectory($filePath, self::IGNORE, $context);
                }
            } else {
                self::unlink($filePath, self::IGNORE, $context);
            }
        }
    }

    private static function removeDirectory(string $path, int $flags, ?StreamContext $context = null): void
    {
        error_clear_last();
        $result = $context !== null
            ? @rmdir($path, $context->getResource())
            : @rmdir($path);

        if ($result === false && (!($flags & self::IGNORE) || is_dir($path))) {
            throw FilesystemException::create("Cannot remove directory", $path, $context, error_get_last());
        }
    }

    /**
     * Copy files in directory (and subdirectories with RECURSIVE)
     * Copy linked content instead of linking when FOLLOW_SYMLINKS is set
     * Ignores error when IGNORE is set and target directory already exists (overwrites contents)
     *
     * @param string|Path $source
     * @param string|Path $destination
     * @param int $flags
     * @param callable|null $filter
     * @param StreamContext|null $context
     */
    public static function copyDirectory($source, $destination, int $flags = 0, ?callable $filter = null, ?StreamContext $context = null): void
    {
        Check::flags($flags, self::IGNORE | self::RECURSIVE | self::FOLLOW_SYMLINKS);

        if ($source instanceof Path) {
            $source = $source->getPath();
        }
        if ($destination instanceof Path) {
            $destination = $destination->getPath();
        }
        $source = self::normalizePath($source);
        $destination = self::normalizePath($destination);

        self::createDirectory($destination, $flags & self::IGNORE);

        $items = self::scanDirectory($source, $flags & ~self::IGNORE);
        foreach ($items as $filePath => $fileInfo) {
            if ($filter !== null && !$filter($fileInfo)) {
                continue;
            }

            $newPath = self::translatePath($filePath, $source, $destination);
            if ($fileInfo->isDirectory()) {
                self::createDirectory($newPath, self::IGNORE, FilePermissions::ALL, $context);
            } else {
                $dir = dirname($newPath);
                if ($filter !== null && !is_dir($dir)) {
                    self::createDirectory($dir, self::IGNORE);
                }
                self::copy($fileInfo, $newPath, 0, $context);
            }
        }
    }

    /**
     * Returns iterator going through files in directory (and subdirectories with RECURSIVE)
     * Scans linked files/directories when FOLLOW_SYMLINKS is set
     * Returns contents before directory when CHILDREN_FIRST is set (useful when deleting stuff)
     *
     * @param string|Path $path
     * @param int $flags
     * @return Iterator<FileInfo>|FileInfo[]
     */
    public static function scanDirectory($path, int $flags = 0): Iterator
    {
        Check::flags($flags, self::RECURSIVE | self::FOLLOW_SYMLINKS | self::CHILDREN_FIRST);

        if ($path instanceof Path) {
            $path = $path->getPath();
        }

        $iteratorFlags = FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS;
        if ($flags & self::FOLLOW_SYMLINKS) {
            $iteratorFlags |= FilesystemIterator::FOLLOW_SYMLINKS;
        }
        if ($flags & self::RECURSIVE) {
            $iterator = new RecursiveDirectoryIterator($path, $iteratorFlags);
            if ($flags & self::CHILDREN_FIRST) {
                $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);
            } else {
                $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
            }
        } else {
            $iterator = new FilesystemIterator($path, $iteratorFlags);
        }

        return new CallbackIterator($iterator, static function (string $path): FileInfo {
            return new FileInfo($path);
        });
    }

    // permissions -----------------------------------------------------------------------------------------------------

    /**
     * Update permissions of file/directory (and directory contents with RECURSIVE)
     * Including linked content when FOLLOW_SYMLINKS is set
     *
     * @param string|Path $path
     * @param int $add
     * @param int $remove
     * @param int|null $owner
     * @param int|null $group
     * @param int $flags
     * @param callable|null $filter
     */
    public static function updatePermissions(
        $path,
        int $add,
        int $remove,
        ?int $owner = null,
        ?int $group = null,
        int $flags = 0,
        ?callable $filter = null
    ): void
    {
        Check::flags($flags, self::RECURSIVE | self::FOLLOW_SYMLINKS);
        Check::flags($add, FilePermissions::ALL);
        Check::flags($remove, FilePermissions::ALL);

        if (!$path instanceof FileInfo) {
            $path = new FileInfo($path);
        }
        self::setPermissions($path->getPath(), $path->getPermissions(), $add, $remove, $owner, $group);

        if (($flags & self::RECURSIVE) === 0) {
            return;
        }

        // todo: symlinks
        //$updateLinks = !($flags & self::FOLLOW_SYMLINKS);

        $items = self::scanDirectory($path, $flags);
        foreach ($items as $filePath => $fileInfo) {
            if ($filter !== null && !$filter($fileInfo)) {
                continue;
            }

            self::setPermissions($filePath, $fileInfo->getPermissions(), $add, $remove, $owner, $group);
        }
    }

    private static function setPermissions(string $path, int $permissions, int $add, int $remove, ?int $owner, ?int $group): void
    {
        if ($add !== 0 || $remove !== 0) {
            $permissions |= $add;
            $permissions &= ~$remove;

            error_clear_last();
            $result = @chmod($path, $permissions);
            if ($result === false) {
                throw FilesystemException::create("Cannot update permissions", $path, null, error_get_last());
            }
        }

        if ($owner !== null) {
            error_clear_last();
            $result = @chown($path, $owner);

            if ($result === false) {
                throw FilesystemException::create("Cannot change owner", $path, null, error_get_last());
            }

            /*
            if ($updateLinks && $fileInfo->isLink()) {
                error_clear_last();
                $result = @lchown($path, $owner);

                if ($result === false) {
                    throw FilesystemException::create("Cannot change link owner", $path, null, error_get_last());
                }
            }
            */
        }

        if ($group !== null) {
            error_clear_last();
            $result = @chgrp($path, $group);

            if ($result === false) {
                throw FilesystemException::create("Cannot change group", $path, null, error_get_last());
            }

            /*
            if ($updateLinks && $fileInfo->isLink()) {
                error_clear_last();
                $result = @lchgrp($path, $group);

                if ($result === false) {
                    throw FilesystemException::create("Cannot change link group", $path, null, error_get_last());
                }
            }
            */
        }
    }

}
